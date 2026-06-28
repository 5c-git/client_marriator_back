<?php

namespace Modules\YandexSmena\Services\Mappers;

use Modules\YandexSmena\Models\SmenaPayment;

class PaymentMapper
{
    public function toPayload(SmenaPayment $payment): array
    {
        $payload = [
            'name' => $payment->name,
        ];

        if ($payment->external_id !== null) {
            $payload['id'] = $payment->external_id;
        }

        return $payload;
    }
}
