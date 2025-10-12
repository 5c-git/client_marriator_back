<?php
namespace App\Services\DocumentServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;

class CorrectRecognitionService
{
    protected string $baseUrl = 'https://extractor.correct.su';
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.correct_recognition.token');
    }

    public function createPackage(?string $catalogId = null): ?int
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/api/packages", [
                'nomenclaturesCatalogId' => $catalogId
            ]);

        return $response->successful()
            ? $response->json()['packageId']
            : null;
    }

    public function uploadImage(int $packageId, string $imagePath): ?int
    {
        $imagePath = str_replace('/storage','',$imagePath);
        $response = Http::withToken($this->token)
            ->attach('file', Storage::get($imagePath), basename($imagePath))
            ->post("{$this->baseUrl}/api/images/Package/{$packageId}");

        return $response->successful()
            ? $response->json()['imageIds'][0]
            : null;
    }

    public function startRecognition(
        int $packageId,
        ?string $callbackUrl = null
    ): bool {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/api/packages/{$packageId}/start", [
                'callbackUrl' => $callbackUrl
            ]);

        return $response->noContent();
    }

    public function getRecognitionResult(int $packageId): ?array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/api/packages/{$packageId}");

        return $response->successful()
            ? $response->json()
            : null;
    }

    // Проверка доступности API
    public function ping(): bool
    {
        return Http::withToken($this->token)
            ->get("{$this->baseUrl}/api/packages/ping")
            ->successful();
    }
}
