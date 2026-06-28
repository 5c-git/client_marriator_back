<?php

namespace Modules\YandexSmena\Services\Mappers;

use App\Models\Fields\Directory\ViewActivities;

class ProfessionMapper
{
    public function toPayload(ViewActivities $activity, ?string $externalId = null): array
    {
        $payload = [
            'name' => $activity->name,
        ];

        if ($externalId !== null) {
            $payload['id'] = $externalId;
        }

        return $payload;
    }
}
