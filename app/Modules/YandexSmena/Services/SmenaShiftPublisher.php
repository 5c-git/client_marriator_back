<?php

namespace Modules\YandexSmena\Services;

use Modules\YandexSmena\Exceptions\YandexSmenaConfigurationException;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Services\Mappers\ShiftMapper;

class SmenaShiftPublisher
{
    private const ENTITY_TYPE = 'shift';

    public function __construct(
        private readonly YandexSmenaEventPublisher $publisher,
        private readonly ShiftMapper $shiftMapper,
    ) {
    }

    public function create(SmenaShift $shift): void
    {
        $payload = $this->shiftMapper->toPayload([
            'entity_id' => $shift->entity_id,
            'site_id' => $this->requireExternalId($shift->site, 'site'),
            'profession_id' => $this->requireExternalId($shift->profession, 'profession'),
            'payment_id' => $this->requireExternalId($shift->payment, 'payment'),
            'start_at' => $shift->start_at,
            'length_min' => $shift->length_min,
            'rest_length_min' => $shift->rest_length_min,
        ]);

        $shift->update([
            'payload' => $payload,
            'published_at' => now(),
            'sync_error' => null,
        ]);

        $this->publisher->publish('provider.shift.create', self::ENTITY_TYPE, $shift->entity_id, $payload);
    }

    public function cancel(SmenaShift $shift, string $reason, ?string $comment = null): void
    {
        $this->ensureValidReason($reason, ['creation_mistake', 'service_not_needed', 'worker_not_found', 'other']);

        $payload = ['reason' => $reason];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish('provider.shift.cancel', self::ENTITY_TYPE, $shift->entity_id, $payload);
    }

    public function resume(SmenaShift $shift, ?string $comment = null): void
    {
        $payload = [];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish('provider.shift.resume', self::ENTITY_TYPE, $shift->entity_id, $payload);
    }

    private function requireExternalId($model, string $type): string
    {
        $externalId = $model?->external_id;

        if ($externalId === null || $externalId === '') {
            throw new YandexSmenaConfigurationException(
                "Missing Yandex.Smena external_id for {$type} [id: {$model?->id}]"
            );
        }

        return $externalId;
    }

    private function ensureValidReason(string $reason, array $allowed): void
    {
        if (! in_array($reason, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid reason '{$reason}'. Allowed: ".implode(', ', $allowed));
        }
    }
}
