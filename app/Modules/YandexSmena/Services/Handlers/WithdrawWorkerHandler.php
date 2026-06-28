<?php

namespace Modules\YandexSmena\Services\Handlers;

use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaShift;

class WithdrawWorkerHandler implements SmenaEventHandlerInterface
{
    public function handle(array $event): void
    {
        $shift = $this->resolveShift($event['entity_id'] ?? null);

        if ($shift === null) {
            return;
        }

        $workerId = $event['payload']['worker_id'] ?? null;

        if ($workerId === null) {
            return;
        }

        SmenaCandidate::query()
            ->where('yandex_smena_shift_id', $shift->id)
            ->where('external_worker_id', $workerId)
            ->update(['status' => 'withdrawn']);

        $shift->update(['external_status' => 'available']);
    }

    private function resolveShift(?string $entityId): ?SmenaShift
    {
        if ($entityId === null) {
            return null;
        }

        return SmenaShift::query()->where('entity_id', $entityId)->first();
    }
}
