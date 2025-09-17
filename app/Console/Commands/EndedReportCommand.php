<?php

namespace App\Console\Commands;


use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Report;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class EndedReportCommand extends Command
{
    protected $signature = 'endedReportCommand';

    protected $description = '';


    public function handle(): void
    {
        $report = Report::query()
            ->where('status',ReportStatusEnum::start->value)
            ->first();

    }

}
