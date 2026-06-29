<?php

namespace Modules\YandexSmena\Services;

use App\Models\Order\OrderActivities;
use App\Models\Order\TaskActivity;
use Carbon\Carbon;
use InvalidArgumentException;
use Modules\YandexSmena\Exceptions\YandexSmenaConfigurationException;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Models\SmenaProfession;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Models\SmenaSite;

class SmenaShiftFactory
{
    public function __construct(private readonly SmenaShiftPublisher $publisher)
    {
    }

    /**
     * Build and publish Yandex.Smena shifts from an OrderActivities or TaskActivity.
     *
     * @return array<int, SmenaShift>
     */
    public function fromActivity(OrderActivities|TaskActivity $activity): array
    {
        $site = $this->resolveSite($activity);
        $profession = $this->resolveProfession($activity);
        $payment = $this->resolvePayment($profession);

        $startAt = $activity->date_start;
        $endAt = $activity->date_end;

        if ($startAt === null || $endAt === null) {
            throw new InvalidArgumentException('Activity must have date_start and date_end');
        }

        $lengthMin = (int) $startAt->diffInMinutes($endAt, false);

        if ($lengthMin <= 0) {
            throw new InvalidArgumentException('Activity length must be greater than 0 minutes');
        }

        $restLengthMin = $profession->rest_length_min ?? 0;

        if ($restLengthMin >= $lengthMin) {
            throw new InvalidArgumentException('Rest length must be less than total activity length');
        }

        $count = (int) ($activity->count ?? 1);

        if ($count <= 0) {
            throw new InvalidArgumentException('Activity count must be at least 1');
        }

        $shifts = [];

        for ($i = 0; $i < $count; $i++) {
            $shift = $this->createShift(
                $activity,
                $site->id,
                $profession->id,
                $payment->id,
                $startAt,
                $lengthMin,
                $restLengthMin
            );

            $this->publisher->create($shift);

            $shifts[] = $shift;
        }

        return $shifts;
    }

    private function createShift(
        OrderActivities|TaskActivity $activity,
        int $siteId,
        int $professionId,
        int $paymentId,
        Carbon $startAt,
        int $lengthMin,
        int $restLengthMin
    ): SmenaShift {
        $attributes = [
            'entity_id' => Uuid7::generate(),
            'yandex_smena_site_id' => $siteId,
            'yandex_smena_profession_id' => $professionId,
            'yandex_smena_payment_id' => $paymentId,
            'start_at' => $startAt,
            'length_min' => $lengthMin,
            'rest_length_min' => $restLengthMin,
            'payload' => [],
        ];

        if ($activity instanceof OrderActivities) {
            $attributes['order_id'] = $activity->order_id;
            $attributes['order_activity_id'] = $activity->id;
        } else {
            $attributes['task_id'] = $activity->task_id;
            $attributes['task_activity_id'] = $activity->id;
            $attributes['order_activity_id'] = $activity->order_activity_id;
        }

        return SmenaShift::create($attributes);
    }

    private function resolveSite(OrderActivities|TaskActivity $activity): SmenaSite
    {
        $placeId = $activity instanceof OrderActivities
            ? $activity->order?->place_id
            : $activity->task?->place_id;

        if ($placeId === null) {
            throw new YandexSmenaConfigurationException('Activity has no linked place');
        }

        $site = SmenaSite::query()->where('place_id', $placeId)->first();

        if ($site === null) {
            throw new YandexSmenaConfigurationException("No Yandex.Smena site mapping for place [id: {$placeId}]");
        }

        return $site;
    }

    private function resolveProfession(OrderActivities|TaskActivity $activity): SmenaProfession
    {
        $profession = SmenaProfession::query()
            ->where('view_activity_id', $activity->view_activity_id)
            ->first();

        if ($profession === null) {
            throw new YandexSmenaConfigurationException(
                "No Yandex.Smena profession mapping for view_activity [id: {$activity->view_activity_id}]"
            );
        }

        return $profession;
    }

    private function resolvePayment(SmenaProfession $profession): SmenaPayment
    {
        $paymentId = $profession->yandex_smena_payment_id;

        if ($paymentId === null) {
            throw new YandexSmenaConfigurationException(
                "SmenaProfession [id: {$profession->id}] has no Yandex.Smena payment"
            );
        }

        $payment = SmenaPayment::query()->where('id', $paymentId)->first();

        if ($payment === null) {
            throw new YandexSmenaConfigurationException(
                "Yandex.Smena payment [id: {$paymentId}] not found for SmenaProfession [id: {$profession->id}]"
            );
        }

        return $payment;
    }
}
