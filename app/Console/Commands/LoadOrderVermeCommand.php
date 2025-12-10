<?php

namespace App\Console\Commands;

use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\PVP\PVPService;
use App\Services\PVP\TimeBook\TimeBookService;
use App\Services\PVP\Verme\VermeService;
use Illuminate\Console\Command;
use App\Enum\Document\RecognitionDocumentStatusEnum;

class LoadOrderVermeCommand extends Command
{
    protected $signature = 'loadOrderVerme';
    protected $description = 'Process loading order from pvp';

    public function handle(): void
    {
        $pvpService = new PVPService(new VermeService());
        $pvpService->startLoad();
    }
}
