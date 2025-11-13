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

class ArchiveBid extends Command
{
    protected $signature = 'archiveBid';

    protected $description = '';

    public function handle(): void
    {
//        $orders = Bid::where('date_end', '<', now())
//        ->with('user')->inRandomOrder()->limit(10)->get();
//
//        $value = Setting::getValue('leave_bid');
//        foreach ($orders as $order){
//            /** @var Bid $order */
//            if(!empty($order->user->leave_bid)){
//                $defaultValue = $order->user->leave_bid;
//            }else{
//                $defaultValue = $value;
//            }
//
//            $timeToSubtract = Carbon::parse($defaultValue);
//
//            $timeAfterPeriod = $order->date_end->addHours($timeToSubtract->hour)
//                ->addMinutes($timeToSubtract->minute);
//
//            if($timeAfterPeriod?->gt(Carbon::now())){
//                $order->status = OrderStatusEnum::archive->value;
//                $order->save();
//            }
//        }
    }
}
