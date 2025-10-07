<?php

namespace App\Console\Commands;

use App\Enum\Document\DocumentErrorText;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\DocumentServices\RecognitionDocumentService;
use Illuminate\Console\Command;

class SendUserFileToCorrect extends Command
{
    protected $signature = 'sendUserFileToCorrect';

    protected $description = '';

    public function handle(): void
    {
        $recognitionDocuments = RecognitionDocument::query()
            ->where('status', RecognitionDocumentStatusEnum::pending->value)
            ->limit(10)->get();
        foreach ($recognitionDocuments as $recognitionDocument) {
            try {
                /** @var RecognitionDocument $recognitionDocument */
                $correct   = new CorrectRecognitionService();
                $packageId = $correct->createPackage();
                if ($packageId) {
                    $recognitionDocument->external_package_id = $packageId;
                    $fileUrl                                  = $recognitionDocument->link;
                    $dataUpload                               = $correct->uploadImage($packageId, $fileUrl);
                    if ($dataUpload) {
                        $start = $correct->startRecognition($packageId);
                        if ($start) {
                            $recognitionDocument->status = RecognitionDocumentStatusEnum::processing->value;
                        }
                    } else {
                        $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                        RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorUpload->getUserBinding());
                    }
                    $recognitionDocument->save();
                }
            } catch (\Throwable $e) {
                $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorPhp->getUserBinding());
                $recognitionDocument->save();
            }
        }
    }
}
