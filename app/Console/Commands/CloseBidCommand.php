<?php

namespace App\Console\Commands;


use App\Enum\Order\ReportStatusEnum;
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
        $reports = Report::query()
            ->where('status',ReportStatusEnum::start->value)
            ->where('date_end',null)
            ->where('date_auto_close','<=',Carbon::now())
            ->get();
        foreach ($reports as $report){
            $report->status = ReportStatusEnum::notEnded->value;
            $report->save();
        }
    }
}
