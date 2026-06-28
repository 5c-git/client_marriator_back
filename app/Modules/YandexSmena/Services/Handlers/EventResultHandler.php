<?php

namespace Modules\YandexSmena\Services\Handlers;

use Illuminate\Support\Facades\Log;
use Modules\YandexSmena\Models\SmenaEventLog;
use Modules\YandexSmena\Models\SmenaShift;

class EventResultHandler implements SmenaEventHandlerInterface
{
    private const STATUS_MAP = [
        'provider.shift.create' => 'available',
        'provider.shift.cancel' => 'canceled',
        'provider.shift.resume' => 'available',
        'provider.shift.reject_worker' => 'available',
        'provider.shift.start' => 'started',
        'provider.shift.approve_worker' => 'assigned',
    ];

    public function handle(array $event): void
    {
        $payload = $event['payload'] ?? [];
        $sourceEventId = $payload['source_event_id'] ?? null;

        if ($sourceEventId === null) {
            return;
        }

        $sourceLog = SmenaEventLog::query()->where('event_id', $sourceEventId)->first();

        if ($sourceLog !== null) {
            $sourceLog->update([
                'response' => $payload,
                'processed_at' => now(),
            ]);
        }

        if (($payload['status'] ?? null) !== 'success') {
            $this->recordError($event, $sourceLog);

            return;
        }

        $this->applySuccessStatus($event, $sourceLog);
    }

    private function recordError(array $event, ?SmenaEventLog $sourceLog): void
    {
        $error = ($event['payload']['error_message'] ?? 'Yandex.Smena reported an error').
            ' ['.($event['payload']['error_code'] ?? 'unknown').']';

        if ($sourceLog !== null) {
            $sourceLog->update(['error' => $error]);
        }

        $shift = $this->resolveShift($sourceLog);

        if ($shift !== null) {
            $shift->update(['sync_error' => $error]);
        }

        Log::channel('single')->error('Yandex.Smena event result error', [
            'event' => $event,
            'source_event_id' => $sourceLog?->event_id,
        ]);
    }

    private function applySuccessStatus(array $event, ?SmenaEventLog $sourceLog): void
    {
        $shift = $this->resolveShift($sourceLog);

        if ($shift === null) {
            return;
        }

        $sourceEventType = $event['payload']['source_event_type'] ?? $sourceLog?->event_type;

        if ($sourceEventType === 'provider.shift.set_fact') {
            $isAbsent = $sourceLog?->payload['payload']['is_absent'] ?? false;
            $shift->update([
                'external_status' => $isAbsent ? 'canceled' : 'finished',
                'sync_error' => null,
            ]);

            return;
        }

        if ($sourceEventType !== null && isset(self::STATUS_MAP[$sourceEventType])) {
            $shift->update([
                'external_status' => self::STATUS_MAP[$sourceEventType],
                'sync_error' => null,
            ]);
        }
    }

    private function resolveShift(?SmenaEventLog $sourceLog): ?SmenaShift
    {
        if ($sourceLog === null) {
            return null;
        }

        $entityId = $sourceLog->entity_id;

        if ($sourceLog->entity_type !== 'shift' || $entityId === null) {
            return null;
        }

        return SmenaShift::query()->where('entity_id', $entityId)->first();
    }
}
