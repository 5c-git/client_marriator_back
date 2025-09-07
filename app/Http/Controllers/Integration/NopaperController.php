<?php

namespace App\Http\Controllers\Integration;

use Illuminate\Http\Request;
use App\Events\DocumentStatusUpdated;
use App\Services\Nopaper\NopaperService;

class NopaperController extends Controller
{

    protected $nopaperService;
    public function __construct(NopaperService $nopaperService)
    {
        $this->nopaperService = $nopaperService;
    }

    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $request->header('X-Event-Type');

        switch ($eventType) {
            case 'document.signed':
                event(new DocumentStatusUpdated($payload['documentId'], 'signed'));
                break;
            case 'document.rejected':
                event(new DocumentStatusUpdated($payload['documentId'], 'rejected'));
                break;
            case 'document.revoked':
                event(new DocumentStatusUpdated($payload['documentId'], 'revoked'));
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Создание и отправка документа на подписание
     */
    public function createAndSendDocument(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'userGuid' => 'required|string',
            'recipients' => 'required|array',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);

        try {
            // Создаем черновик документа
            $draftData = [
                'title' => $validated['title'],
                'userGuid' => $validated['userGuid'],
                'recipientInfoList' => $validated['recipients'],
                'documentRouteType' => 2,
                'isDisableChange' => true
            ];

            $draftResponse = $this->nopaperService->createDraft($draftData);
            $documentId = $draftResponse['documentId'];

            // Прикрепляем файл
            $fileContent = base64_encode(file_get_contents($validated['file']->getRealPath()));
            $fileData = [
                'fileInfo' => [
                    'fileNameWithExtension' => $validated['file']->getClientOriginalName(),
                    'filebase64' => $fileContent
                ]
            ];

            $this->nopaperService->attachFileToDocument($documentId, $fileData);

            // Отправляем документ в работу
            $sendResponse = $this->nopaperService->sendDocument($documentId);

            return response()->json([
                'success' => true,
                'documentId' => $documentId,
                'message' => 'Документ успешно отправлен на подписание'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке документа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка статуса документа
     */
    public function checkDocumentStatus($documentId)
    {
        try {
            $documentInfo = $this->nopaperService->getDocumentInfo($documentId);

            return response()->json([
                'success' => true,
                'status' => $documentInfo['documentStatus'],
                'document' => $documentInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статуса документа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение списка документов с фильтрами
     */
    public function getDocumentsList(Request $request)
    {
        try {
            // Здесь будет реализация получения списка документов с фильтрами
            // согласно 13-му пункту вашего API

            return response()->json([
                'success' => true,
                'documents' => [] // Здесь будет реальный список документов
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка документов: ' . $e->getMessage()
            ], 500);
        }
    }
}
