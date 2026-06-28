<?php

namespace Modules\YandexSmena\Services\Handlers;

use Modules\YandexSmena\Models\SmenaShift;

class AdjustFactHandler implements SmenaEventHandlerInterface
{
    public function handle(array $event): void
    {
        $shift = $this->resolveShift($event['entity_id'] ?? null);

        if ($shift === null) {
            return;
        }

        $response = $shift->response ?? [];
        $response['adjust_fact'] = $event['payload'] ?? [];

        $shift->update(['response' => $response]);
    }

    private function resolveShift(?string $entityId): ?SmenaShift
    {
        if ($entityId === null) {
            return null;
        }

        return SmenaShift::query()->where('entity_id', $entityId)->first();
    }
}
