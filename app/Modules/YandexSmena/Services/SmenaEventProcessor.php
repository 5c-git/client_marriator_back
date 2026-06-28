<?php

namespace Modules\YandexSmena\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\YandexSmena\Models\SmenaEventLog;
use Modules\YandexSmena\Models\SmenaPollState;
use Modules\YandexSmena\Services\Handlers\SmenaEventHandlerInterface;

class SmenaEventProcessor
{
    private const BATCH_LIMIT = 100;

    /**
     * @param  array<string, class-string<SmenaEventHandlerInterface>>  $handlers
     */
    public function __construct(
        private readonly YandexSmenaApiClientInterface $client,
        private readonly array $handlers,
    ) {
    }

    public function run(): void
    {
        $cursor = SmenaPollState::cursor();
        $lastSuccessfulEventId = null;

        do {
            $batch = $this->client->pollEvents($cursor, self::BATCH_LIMIT);

            foreach ($batch['events'] as $event) {
                $this->process($event);
                $lastSuccessfulEventId = $event['event_id'] ?? null;
            }

            SmenaPollState::updateCursor($lastSuccessfulEventId);
            $cursor = $lastSuccessfulEventId;
        } while ($batch['has_next'] ?? false);
    }

    private function process(array $event): void
    {
        $eventId = $event['event_id'] ?? null;
        $eventType = $event['event_type'] ?? null;

        if ($eventId === null || $eventType === null) {
            Log::channel('single')->warning('Yandex.Smena polled event missing required fields', $event);

            return;
        }

        if ($this->alreadyProcessed($eventId)) {
            return;
        }

        $this->touchShift($event);

        $handlerClass = $this->handlers[$eventType] ?? null;

        if ($handlerClass === null) {
            Log::channel('single')->info('Yandex.Smena no handler for event type', ['event_type' => $eventType]);

            $this->markProcessed($event);

            return;
        }

        $handler = app($handlerClass);

        if (! $handler instanceof SmenaEventHandlerInterface) {
            Log::channel('single')->error('Yandex.Smena handler does not implement interface', [
                'handler' => $handlerClass,
            ]);

            return;
        }

        try {
            $handler->handle($event);
        } catch (\Throwable $e) {
            $this->markProcessed($event, $e->getMessage());

            Log::channel('single')->error('Yandex.Smena event handler failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->markProcessed($event);
    }

    private function alreadyProcessed(string $eventId): bool
    {
        return SmenaEventLog::query()
            ->where('source_event_id', $eventId)
            ->whereNull('error')
            ->exists();
    }

    private function markProcessed(array $event, ?string $error = null): void
    {
        SmenaEventLog::query()->updateOrCreate(
            ['source_event_id' => $event['event_id']],
            [
                'event_id' => (string) \Illuminate\Support\Str::uuid(),
                'event_type' => $event['event_type'],
                'event_ts' => $event['event_ts'] ?? Carbon::now('UTC')->toIso8601ZuluString('microsecond'),
                'direction' => 'in',
                'entity_type' => $event['entity_type'] ?? null,
                'entity_id' => $event['entity_id'] ?? null,
                'payload' => $event,
                'error' => $error,
                'processed_at' => now(),
            ]
        );
    }

    private function touchShift(array $event): void
    {
        $entityType = $event['entity_type'] ?? null;
        $entityId = $event['entity_id'] ?? null;

        if ($entityType !== 'shift' || $entityId === null) {
            return;
        }

        \Modules\YandexSmena\Models\SmenaShift::query()
            ->where('entity_id', $entityId)
            ->update([
                'last_poll_at' => now(),
                'last_source_event_id' => $event['event_id'] ?? null,
            ]);
    }
}
