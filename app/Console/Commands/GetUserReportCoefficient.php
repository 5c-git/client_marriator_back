<?php

namespace App\Console\Commands;

use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Report;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\PVP\PVPService;
use Illuminate\Console\Command;

class GetUserReportCoefficient extends Command
{
    protected $signature = 'getUserReportCoefficient';

    protected $description = '';

    public function handle(): void
    {
        $reports = Report::query()
            ->where('status',ReportStatusEnum::reported->value)
            ->where('pvp',true)
            ->get();
        foreach ($reports as $report) {
            PVPService::getResultWork($report);
        }
    }
}
