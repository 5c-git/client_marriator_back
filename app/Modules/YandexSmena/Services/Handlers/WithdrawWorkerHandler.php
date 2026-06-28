<?php

namespace Modules\YandexSmena\Services\Handlers;

use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaShift;

class WithdrawWorkerHandler implements SmenaEventHandlerInterface
{
    private const FAVORITE_REASONS = [
        'favorite_not_found',
        'favorite_not_confirmed',
        'favorite_has_intersection',
    ];

    public function handle(array $event): void
    {
        $shift = $this->resolveShift($event['entity_id'] ?? null);

        if ($shift === null) {
            return;
        }

        $payload = $event['payload'] ?? [];
        $workerId = $payload['worker_id'] ?? null;
        $reason = $payload['reason'] ?? null;

        if ($workerId === null) {
            return;
        }

        SmenaCandidate::query()
            ->where('yandex_smena_shift_id', $shift->id)
            ->where('external_worker_id', $workerId)
            ->update(['status' => 'withdrawn']);

        $status = $this->resolveShiftStatus($shift, $reason);

        $shift->update(['external_status' => $status]);
    }

    private function resolveShiftStatus(SmenaShift $shift, ?string $reason): string
    {
        $favoritesOnly = $shift->payload['favorites_only'] ?? false;

        if ($favoritesOnly && in_array($reason, self::FAVORITE_REASONS, true)) {
            return 'canceled';
        }

        return 'available';
    }

    private function resolveShift(?string $entityId): ?SmenaShift
    {
        if ($entityId === null) {
            return null;
        }

        return SmenaShift::query()->where('entity_id', $entityId)->first();
    }
}
