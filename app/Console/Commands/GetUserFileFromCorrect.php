<?php

namespace App\Console\Commands;

use App\Enum\Document\DocumentErrorText;
use App\Enum\Document\DocumentFieldTypeEnum;
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
                $recognitionDocument->unprocessed_data = json_encode($resData, JSON_UNESCAPED_UNICODE);
                if ($resData && !empty($resData['state']) && $resData['state'] === 'Recognized') {
                    if(!empty($resData["documents"])) {
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
                        if (!empty($docData['docType']) && $docTypeEnum = DocumentTypeEnum::getEnumByExternalName(
                                $docData['docType']
                            )) {
                            if($docTypeEnum == DocumentFieldTypeEnum::tryFrom($recognitionDocument->file_field)?->geFieldType()) {
                                $recognitionDocument->file_type = $docTypeEnum->value;
                            }else{
                                $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                                RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorFileType->getUserBinding());
                                $recognitionDocument->save();
                                break;
                            }
                        }
                        $recognitionDocument->save();
                        RecognitionDocumentService::createUserMoreInformationInfoFromDocument($recognitionDocument);

                        if(
                            ($recognitionDocument->status == RecognitionDocumentStatusEnum::recognized->value &&
                            $recognitionDocument->file_type == DocumentTypeEnum::Passport->value) || ($recognitionDocument->status == RecognitionDocumentStatusEnum::recognized &&
                                $recognitionDocument->file_type == DocumentTypeEnum::Passport)
                        ){
                            $user = $recognitionDocument->user;
                            if(is_array($user->data)){
                                $dataForDoc = $user->data;
                            }else{
                                $dataForDoc = json_decode($user->data, true);
                            }
                            if(!empty($dataForDoc)) {
                                (new RecognitionDocumentService($dataForDoc, $user))->createDocument();
                            }
                        }

                    }else{
                        $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                        RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorRecognize->getUserBinding());
                        $recognitionDocument->save();
                    }
                }
                $recognitionDocument->save();
            } catch (\Throwable $e) {
                $recognitionDocument->status = RecognitionDocumentStatusEnum::failed->value;
                RecognitionDocumentService::addErrorField($recognitionDocument,DocumentErrorText::ErrorPhp->getUserBinding());
                $recognitionDocument->save();
            }
        }
    }
}
