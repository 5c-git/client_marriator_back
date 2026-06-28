<?php

namespace Modules\YandexSmena\Services\Mappers;

use Carbon\Carbon;

class ShiftMapper
{
    /**
     * @param array{
     *     site_id: string,
     *     profession_id: string,
     *     payment_id: string,
     *     start_at: Carbon,
     *     length_min: int,
     *     rest_length_min?: int,
     *     favorites_worker_ids?: string[],
     *     favorites_only?: bool,
     * } $data
     */
    public function toPayload(array $data): array
    {
        $payload = [
            'site_id' => $data['site_id'],
            'profession_id' => $data['profession_id'],
            'payment_id' => $data['payment_id'],
            'start_at' => $data['start_at']->copy()->utc()->toIso8601ZuluString(),
            'length' => (int) $data['length_min'],
            'rest_length' => (int) ($data['rest_length_min'] ?? 0),
        ];

        if (! empty($data['favorites_worker_ids'])) {
            $payload['favorites_worker_ids'] = $data['favorites_worker_ids'];
        }

        if (isset($data['favorites_only'])) {
            $payload['favorites_only'] = (bool) $data['favorites_only'];
        }

        return $payload;
    }
}
