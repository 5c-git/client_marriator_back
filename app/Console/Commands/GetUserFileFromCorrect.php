<?php

namespace App\Console\Commands;

use App\Enum\Document\DocumentErrorText;
use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\DocumentServices\RecognitionDocumentService;
use Illuminate\Console\Command;

class GetUserFileFromCorrect extends Command
{
    protected $signature = 'getUserFileFromCorrect';

    protected $description = '';

    public function handle(): void
    {
        $recognitionDocuments = RecognitionDocument::query()
            ->where('status', RecognitionDocumentStatusEnum::processing->value)
            ->limit(10)->get();
        foreach ($recognitionDocuments as $recognitionDocument) {
            try {
                /** @var RecognitionDocument $recognitionDocument */
                $correct = new CorrectRecognitionService();
                $resData = $correct->getRecognitionResult($recognitionDocument->external_package_id);
                if ($resData && !empty($resData["documents"])) {
                    $docData = [];
                    foreach ($resData["documents"] as $document) {
                        $docData['docType'] = $document["docType"];
                        if (!empty($document["fields"])) {
                            foreach ($document["fields"] as $field) {
                                $docData[$field["fieldKey"]] = $field["fieldValue"];
                            }
                        }
                    }
                    $recognitionDocument->data = $docData;
                    $recognitionDocument->status = RecognitionDocumentStatusEnum::recognized->value;
                    if (!empty($docData['docType']) && $docTypeEnum = DocumentTypeEnum::getEnumByExternalName($docData['docType'])) {
                        $recognitionDocument->file_type = $docTypeEnum->value;
                    }
                    $recognitionDocument->save();
                    RecognitionDocumentService::createUserMoreInformationInfoFromDocument($recognitionDocument);
                }
            } catch (\Throwable $e) {
                $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorPhp->getUserBinding());
                $recognitionDocument->save();
            }
        }
    }
}
