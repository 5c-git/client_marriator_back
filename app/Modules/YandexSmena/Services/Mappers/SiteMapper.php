<?php

namespace Modules\YandexSmena\Services\Mappers;

use App\Models\Fields\Directory\Place;

class SiteMapper
{
    public function toPayload(Place $place, ?string $externalId = null): array
    {
        $payload = [
            'name' => $place->name,
        ];

        if ($place->address_kladr !== null && $place->address_kladr !== '') {
            $payload['address'] = $place->address_kladr;
        }

        if ($place->latitude !== null && $place->longitude !== null) {
            $payload['latitude'] = (float) $place->latitude;
            $payload['longitude'] = (float) $place->longitude;
        }

        if ($externalId !== null) {
            $payload['id'] = $externalId;
        }

        return $payload;
    }
}
