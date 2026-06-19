<?php

namespace Modules\Questionnaire\Services\Steps;

use Modules\Questionnaire\Models\Questionnaire;

class EnrichGeoDataStep implements QuestionnaireStepInterface
{
    public function name(): string
    {
        return 'enrichment.geo_data';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function handle(Questionnaire $questionnaire): void
    {
        $data = $questionnaire->data;

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (! empty($latitude) && ! empty($longitude)) {
            // Placeholder for geo normalization / enrichment.
            $data['geo_formatted'] = "{$latitude},{$longitude}";
        }

        $data['enriched_at'] = now()->toDateTimeString();
        $questionnaire->data = $data;
    }
}
