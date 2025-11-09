<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class ArciveOrder extends Command
{
    protected $signature = 'archiveOrder';

    protected $description = '';

    public function handle(): void
    {
        $orders = Order::whereHas('orderActivities', function ($query) {
            $query->where('date_end', '<', now());
        })->with('user')->inRandomOrder()->limit(10)->get();

        $value = Setting::getValue('live_order');
        foreach ($orders as $order){
            /** @var Order $order */
            if(!empty($order->user->live_order)){
                $defaultValue = $order->user->live_order;
            }else{
                $defaultValue = $value;
            }
            $activities = $order->orderActivities()
                ->orderBy('date_end','desc')
                ->first();
            $timeToSubtract = Carbon::parse($defaultValue);
            /** @var OrderActivities $activities */
            $timeAfterPeriod = $activities?->date_end->addHours($timeToSubtract->hour)
                ->addMinutes($timeToSubtract->minute);

            if($timeAfterPeriod?->gt(Carbon::now())){
                $order->status = OrderStatusEnum::archive->value;
                $order->save();
            }
        }
    }
}
