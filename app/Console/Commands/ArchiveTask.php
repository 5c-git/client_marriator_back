<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Report;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Models\Setting;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class ArchiveTask extends Command
{
    protected $signature = 'archiveTask';

    protected $description = '';

    public function handle(): void
    {
        $orders = Task::whereHas('taskActivities', function ($query) {
            $query->where('date_end', '<', now());
        })->where('status','!=',OrderStatusEnum::archive->value)->with('user')->inRandomOrder()->limit(10)->get();

        $value = Setting::getValue('live_task');
        foreach ($orders as $order){
            /** @var Task $order */
            if(!empty($order->user->live_task)){
                $defaultValue = $order->user->live_task;
            }else{
                $defaultValue = $value;
            }
            $activities = $order->taskActivities()
                ->orderBy('date_end','desc')
                ->first();
            $timeToSubtract = Carbon::parse($defaultValue);
            /** @var TaskActivity $activities */
            $timeAfterPeriod = $activities?->date_end->addHours($timeToSubtract->hour)
                ->addMinutes($timeToSubtract->minute);

            if($timeAfterPeriod?->gt(Carbon::now())){
                $order->status = OrderStatusEnum::archive->value;
                $order->save();
                if($order->bid){
                    foreach ($order->bid as $bid){
                        $bid->status = OrderStatusEnum::archive->value;
                        $bid->save();
                    }
                }
            }
        }
    }
}
