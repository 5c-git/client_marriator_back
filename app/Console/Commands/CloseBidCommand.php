<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class CloseBidCommand extends Command
{
    protected $signature = 'closeBidCommand';

    protected $description = '';

    public function handle(): void
    {
        $bids = Bid::query()
            ->whereIn('status',[
                OrderStatusEnum::accepted->value,
                OrderStatusEnum::new->value,
                OrderStatusEnum::notAccepted->value
            ])
            ->where('date_end','<',Carbon::now()->subHours(12))
            ->get();
        foreach ($bids as $bid){
            $bid->status = OrderStatusEnum::canceled->value;
            $bid->save();
        }
    }
}
