<?php

namespace Modules\YandexSmena\Services;

use Modules\YandexSmena\Jobs\PublishYandexSmenaEventJob;

class YandexSmenaEventPublisher
{
    public function __construct(
        private readonly string $queue,
        private readonly EventEnvelopeBuilder $envelopeBuilder,
    ) {
    }

    /**
     * Dispatch an outgoing event to the Yandex.Smena queue.
     *
     * @param  string  $eventType  e.g. provider.shift.create
     * @param  string|null  $entityType  e.g. shift
     * @param  string|null  $entityId  Provider-side entity identifier used for idempotency
     * @param  array  $payload  Event payload
     */
    public function publish(string $eventType, ?string $entityType, ?string $entityId, array $payload): void
    {
        $envelope = $this->envelopeBuilder->build($eventType, $entityType, $entityId, $payload);

        PublishYandexSmenaEventJob::dispatch($envelope)->onQueue($this->queue);
    }
}
