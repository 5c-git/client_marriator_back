<?php

namespace Modules\YandexSmena\Services;

use Carbon\Carbon;
use Modules\YandexSmena\Models\SmenaEventLog;

class EventEnvelopeBuilder
{
    /**
     * Build a Yandex.Smena event envelope with a unique idempotency key and
     * monotonically increasing timestamp for the given entity.
     *
     * @return array{
     *     event_id: string,
     *     event_type: string,
     *     event_ts: string,
     *     entity_type?: string,
     *     entity_id?: string,
     *     payload: array,
     * }
     */
    public function build(string $eventType, ?string $entityType, ?string $entityId, array $payload): array
    {
        $envelope = [
            'event_id' => Uuid7::generate(),
            'event_type' => $eventType,
            'event_ts' => $this->nextTimestamp($entityType, $entityId),
            'payload' => $payload,
        ];

        if ($entityType !== null && $entityType !== '') {
            $envelope['entity_type'] = $entityType;
        }

        if ($entityId !== null && $entityId !== '') {
            $envelope['entity_id'] = $entityId;
        }

        return $envelope;
    }

    private function nextTimestamp(?string $entityType, ?string $entityId): string
    {
        $now = Carbon::now('UTC');

        if ($entityType === null || $entityType === '' || $entityId === null || $entityId === '') {
            return $now->toIso8601ZuluString('microsecond');
        }

        $last = SmenaEventLog::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderByDesc('event_ts')
            ->value('event_ts');

        if ($last === null) {
            return $now->toIso8601ZuluString('microsecond');
        }

        $lastTs = Carbon::parse($last, 'UTC');

        if ($now->greaterThanOrEqualTo($lastTs)) {
            return $now->toIso8601ZuluString('microsecond');
        }

        return $lastTs->copy()->addMicrosecond()->toIso8601ZuluString('microsecond');
    }
}
