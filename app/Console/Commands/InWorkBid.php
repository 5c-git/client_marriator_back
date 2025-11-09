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

class ArciveOrder extends Command
{
    protected $signature = 'archiveTask';

    protected $description = '';

    public function handle(): void
    {
        $orders = Bid::where('date_start', '<', now())
            ->where('status',OrderStatusEnum::notAccepted->value)
            ->with('user')->inRandomOrder()->limit(10)->get();

        foreach ($orders as $order){
            $order->status = OrderStatusEnum::accepted->value;
            $order->save();
        }
    }
}
