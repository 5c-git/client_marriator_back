<?php

namespace App\Console\Commands;

use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\PVP\PVPService;
use App\Services\PVP\TimeBook\TimeBookService;
use App\Services\PVP\Verme\VermeService;
use App\Services\PVP\XFive\XFiveService;
use Illuminate\Console\Command;
use App\Enum\Document\RecognitionDocumentStatusEnum;

class LoadOrderXFiveCommand extends Command
{
    protected $signature = 'loadOrderXFive';
    protected $description = 'Process loading order from pvp';

    public function handle(): void
    {
        $pvpService = new PVPService(new XFiveService());
        $pvpService->startLoad();
    }
}
