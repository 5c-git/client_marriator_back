<?php

namespace Modules\YandexSmena\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaShift;

class SmenaShiftLifecycleService
{
    private const ENTITY_TYPE = 'shift';

    public function __construct(private readonly YandexSmenaEventPublisher $publisher)
    {
    }

    public function approveWorker(SmenaShift $shift, string $workerId): void
    {
        $this->setCandidateStatus($shift, $workerId, 'approved');

        $this->publisher->publish(
            'provider.shift.approve_worker',
            self::ENTITY_TYPE,
            $shift->entity_id,
            ['worker_id' => $workerId]
        );
    }

    public function rejectWorker(SmenaShift $shift, string $workerId, string $reason, ?string $comment = null): void
    {
        $this->ensureValidReason($reason, ['banned_by_inn', 'need_other_worker', 'other']);

        $this->setCandidateStatus($shift, $workerId, 'rejected');

        $payload = [
            'worker_id' => $workerId,
            'reason' => $reason,
        ];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish(
            'provider.shift.reject_worker',
            self::ENTITY_TYPE,
            $shift->entity_id,
            $payload
        );
    }

    public function setCode(SmenaShift $shift, string $workerId, string $code, ?Carbon $expiresAt = null): void
    {
        $payload = [
            'worker_id' => $workerId,
            'code' => $code,
        ];

        if ($expiresAt !== null) {
            $payload['expires_at'] = $expiresAt->copy()->utc()->toIso8601ZuluString();
        }

        $this->publisher->publish(
            'provider.shift.set_code',
            self::ENTITY_TYPE,
            $shift->entity_id,
            $payload
        );
    }

    public function start(SmenaShift $shift, string $workerId, Carbon $actualStartAt): void
    {
        $this->publisher->publish(
            'provider.shift.start',
            self::ENTITY_TYPE,
            $shift->entity_id,
            [
                'worker_id' => $workerId,
                'start_at' => $actualStartAt->copy()->utc()->toIso8601ZuluString(),
            ]
        );
    }

    public function setFact(
        SmenaShift $shift,
        string $workerId,
        bool $isAbsent,
        ?int $factLength = null,
        ?int $factRestLength = null
    ): void {
        $payload = [
            'worker_id' => $workerId,
            'is_absent' => $isAbsent,
        ];

        if (! $isAbsent) {
            if ($factLength === null) {
                throw new InvalidArgumentException('fact_length is required when is_absent is false');
            }

            $payload['fact_length'] = $factLength;

            if ($factRestLength !== null) {
                $payload['fact_rest_length'] = $factRestLength;
            }
        }

        $this->publisher->publish(
            'provider.shift.set_fact',
            self::ENTITY_TYPE,
            $shift->entity_id,
            $payload
        );
    }

    public function rateWorker(SmenaShift $shift, string $workerId, int $rating, ?string $comment = null): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Rating must be between 1 and 5');
        }

        $payload = [
            'worker_id' => $workerId,
            'rating' => $rating,
        ];

        if ($comment !== null && $comment !== '') {
            $payload['comment'] = $comment;
        }

        $this->publisher->publish(
            'provider.shift.rate_worker',
            self::ENTITY_TYPE,
            $shift->entity_id,
            $payload
        );
    }

    private function setCandidateStatus(SmenaShift $shift, string $workerId, string $status): void
    {
        SmenaCandidate::query()
            ->where('yandex_smena_shift_id', $shift->id)
            ->where('external_worker_id', $workerId)
            ->update(['status' => $status]);
    }

    private function ensureValidReason(string $reason, array $allowed): void
    {
        if (! in_array($reason, $allowed, true)) {
            throw new InvalidArgumentException("Invalid reason '{$reason}'. Allowed: ".implode(', ', $allowed));
        }
    }
}
