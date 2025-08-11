<?php
namespace App\Console\Commands;

use App\Models\Document\RecognitionDocument;
use App\Services\DocumentServices\CorrectRecognitionService;
use Illuminate\Console\Command;
use App\Enum\Document\RecognitionDocumentStatusEnum;

class ProcessRecognitionDocuments extends Command
{
    protected $signature = 'recognition:process';
    protected $description = 'Process pending recognition documents';

    public function handle(): void
    {
        $service = app(CorrectRecognitionService::class);

        RecognitionDocument::where('status', RecognitionDocumentStatusEnum::pending->value)->chunk(100, function ($documents) use ($service) {
            foreach ($documents as $document) {
                $this->processDocument($document, $service);
            }
        });

        RecognitionDocument::where('status', RecognitionDocumentStatusEnum::processing->value)->chunk(100, function ($documents) use ($service) {
            foreach ($documents as $document) {
                $this->checkDocumentStatus($document, $service);
            }
        });
    }

    private function processDocument(RecognitionDocument $document, CorrectRecognitionService $service): void
    {
        try {
            // Создаем пакет
            $packageId = $service->createPackage();
            if (!$packageId) {
                throw new \Exception('Package creation failed');
            }

            // Загружаем изображение

            $imagePath = storage_path('app/public/' . $document->link);
            $imageId = $service->uploadImage($packageId, $imagePath);
            if (!$imageId) {
                throw new \Exception('Image upload failed');
            }

            // Запускаем распознавание
            if (!$service->startRecognition($packageId)) {
                throw new \Exception('Recognition start failed');
            }

            $document->update([
                'status' => 'processing',
                'external_package_id' => $packageId
            ]);

        } catch (\Exception $e) {
            $document->update([
                'status' => 'failed',
                'data' => ['error' => $e->getMessage()]
            ]);
        }
    }

    private function checkDocumentStatus(RecognitionDocument $document, CorrectRecognitionService $service): void
    {
        try {
            $result = $service->getRecognitionResult($document->external_package_id);

            if ($result['state'] === 'Recognized') {
                $document->update([
                    'status' => 'recognized',
                    'data' => $result
                ]);
            } elseif (in_array($result['state'], ['Error', 'Expired'])) {
                $document->update([
                    'status' => 'failed',
                    'data' => $result
                ]);
            }
        } catch (\Exception $e) {
            logger()->error($e);
        }
    }
}
