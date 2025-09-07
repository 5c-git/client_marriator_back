<?php

namespace App\Services\Nopaper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NopaperService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.nopaper.base_url');
        $this->apiKey = config('services.nopaper.api_key');
    }

    /**
     * Проверить наличие пользователя
     */
    public function checkUserExists($phone)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v2/external/profile-fl/user-guid/by-phone", [
            'userPhone' => $phone
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Проверить наличие компании
     */
    public function checkCompanyExists($inn)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v2/external/company/by-inn", [
            'inn' => $inn
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Создать черновик документа
     */
    public function createDraft($documentData)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/draft", $documentData);

        return $this->handleResponse($response);
    }

    /**
     * Прикрепить файл к документу
     */
    public function attachFileToDocument($documentId, $fileData)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/{$documentId}/file", $fileData);

        return $this->handleResponse($response);
    }

    /**
     * Отправить документ в работу
     */
    public function sendDocument($documentId)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/{$documentId}/send");

        return $this->handleResponse($response);
    }

    /**
     * Получить информацию о документе
     */
    public function getDocumentInfo($documentId)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v2/external/document/{$documentId}");

        return $this->handleResponse($response);
    }

    /**
     * Обработка ответа API
     */
    protected function handleResponse($response)
    {
        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Remote Sign API Error: ', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception("API request failed: " . $response->body());
    }
}
