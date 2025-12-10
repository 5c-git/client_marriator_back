<?php

namespace App\Console\Commands;

use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\PVP\PVPService;
use App\Services\PVP\TimeBook\TimeBookService;
use Illuminate\Console\Command;
use App\Enum\Document\RecognitionDocumentStatusEnum;

class LoadOrderTimeBookCommand extends Command
{
    protected $signature = 'loadOrderTimeBook';
    protected $description = 'Process loading order from pvp';

    public function handle(): void
    {
        $pvpService = new PVPService(new TimeBookService());
        $pvpService->startLoad();
    }
}
