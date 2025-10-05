<?php

namespace App\Services\Nopaper;

use App\Models\User;
use Illuminate\Http\Client\Response;
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

    public function createUser(array $userData): array
    {
        // Prepare the payload according to the API specification
        $payload = [
            'userPhone' => $userData['phone'],
            'email' => $userData['email'],
            //'name' => $userData['name'],
//            'surname' => $userData['surname'],
//            'patronymic' => $userData['patronymic'] ?? null, // Optional field
//            'isShortTimePassword' => true,
//            'birthDate' => $userData['birth_date'], // Format: "1990-12-27T00:00:00.000Z"
//            'gender' => $userData['gender'], // e.g., 1
//            'passportData' => [
//                'series' => $userData['passport_series'],
//                'number' => $userData['passport_number'],
//                'issuedBy' => $userData['passport_issued_by'],
//                'issuingDate' => $userData['passport_issuing_date'], // Format: "2018-12-27T00:00:00.000Z"
//                'issuerDepartmentCode' => $userData['passport_department_code'],
//                'birthPlace' => $userData['passport_birth_place'],
//            ]
        ];

        // Make the API call
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/v2/external/profile-fl", $payload);

        // Return the API response
        return $this->handleResponse($response);
    }

    /**
     * Проверить наличие пользователя
     */
    public function checkUserExists($phone)
    {
        $response = Http::withHeaders([
            //'X-Api-Key' => $this->apiKey,
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
    protected function handleResponse(Response $response)
    {
        echo "<pre>";
        var_dump($response->body());
        echo "</pre>";
        echo "<pre>";
        var_dump($response->json());
        echo "</pre>";
        echo "<pre>";
        var_dump($response->status());
        echo "</pre>";
        if ($response->successful()) {
           // return $response->json();
        }
        return [];

        Log::error('Remote Sign API Error: ', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception("API request failed: " . $response->body());
    }
}
