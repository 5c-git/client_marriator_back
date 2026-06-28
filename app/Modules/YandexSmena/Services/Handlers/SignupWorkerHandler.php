<?php

namespace Modules\YandexSmena\Services\Handlers;

use Illuminate\Support\Facades\Log;
use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Services\YandexSmenaApiClientInterface;

class SignupWorkerHandler implements SmenaEventHandlerInterface
{
    public function __construct(private readonly YandexSmenaApiClientInterface $client)
    {
    }

    public function handle(array $event): void
    {
        $shift = $this->resolveShift($event['entity_id'] ?? null);

        if ($shift === null) {
            return;
        }

        $payload = $event['payload'] ?? [];
        $workerId = $payload['worker_id'] ?? null;

        if ($workerId === null) {
            return;
        }

        $workerData = $this->fetchWorker($workerId);
        $nameParts = $this->splitFullName($workerData['full_name'] ?? null);

        SmenaCandidate::query()->updateOrCreate(
            [
                'yandex_smena_shift_id' => $shift->id,
                'external_worker_id' => $workerId,
            ],
            [
                'status' => 'pending',
                'last_name' => $nameParts['last_name'],
                'first_name' => $nameParts['first_name'],
                'middle_name' => $nameParts['middle_name'],
                'phone' => $workerData['phone'] ?? null,
                'inn' => $workerData['inn'] ?? null,
                'snils' => $workerData['snils'] ?? null,
                'raw_data' => array_merge($payload, $workerData),
            ]
        );

        $shift->update(['external_status' => 'assigned']);
    }

    private function resolveShift(?string $entityId): ?SmenaShift
    {
        if ($entityId === null) {
            return null;
        }

        return SmenaShift::query()->where('entity_id', $entityId)->first();
    }

    private function fetchWorker(string $workerId): array
    {
        try {
            return $this->client->getWorker($workerId);
        } catch (\Throwable $e) {
            Log::channel('single')->warning('Yandex.Smena failed to fetch worker data', [
                'worker_id' => $workerId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function splitFullName(?string $fullName): array
    {
        if ($fullName === null) {
            return ['last_name' => null, 'first_name' => null, 'middle_name' => null];
        }

        $parts = preg_split('/\s+/', trim($fullName));

        return [
            'last_name' => $parts[0] ?? null,
            'first_name' => $parts[1] ?? null,
            'middle_name' => $parts[2] ?? null,
        ];
    }
}
