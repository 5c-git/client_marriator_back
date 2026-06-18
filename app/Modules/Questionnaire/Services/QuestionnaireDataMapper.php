<?php

namespace Modules\Questionnaire\Services;

use Modules\Questionnaire\Enums\QuestionnaireFieldAlias;

/**
 * Transforms stored questionnaire data (UUID keys) into a normalized array
 * with semantic keys for use by validation/enrichment steps.
 *
 * The original data structure is preserved; this mapper returns an enriched
 * copy that includes both UUID and semantic keys.
 */
class QuestionnaireDataMapper
{
    /**
     * Return a copy of the data with semantic keys added.
     */
    public function map(array $data): array
    {
        $mapped = $data;

        foreach (QuestionnaireFieldAlias::uuidToKeyMap() as $uuid => $key) {
            if (array_key_exists($uuid, $data)) {
                $mapped[$key] = $data[$uuid];
            }
        }

        $mapped['full_name'] = $this->buildFullName($mapped);

        return $mapped;
    }

    private function buildFullName(array $data): ?string
    {
        $parts = array_filter([
            $data['last_name'] ?? null,
            $data['first_name'] ?? null,
            $data['patronymic'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return $parts !== [] ? implode(' ', $parts) : null;
    }
}
