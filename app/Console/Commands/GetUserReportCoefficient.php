<?php

namespace App\Console\Commands;

use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Report;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\Verme\VermeService;
use Illuminate\Console\Command;

class GetUserReportCoefficient extends Command
{
    protected $signature = 'getUserReportCoefficient';

    protected $description = '';

    public function handle(): void
    {
        $reports = Report::query()->where('status',ReportStatusEnum::end->value)->get();
        foreach ($reports as $report) {
            VermeService::updateReportStat($report);
        }
    }
}
