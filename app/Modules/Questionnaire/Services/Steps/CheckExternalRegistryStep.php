<?php

namespace Modules\Questionnaire\Services\Steps;

use Modules\Questionnaire\Exceptions\QuestionnaireProcessingException;
use Modules\Questionnaire\Models\Questionnaire;

class CheckExternalRegistryStep implements QuestionnaireStepInterface
{
    public function name(): string
    {
        return 'external.registry_check';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function handle(Questionnaire $questionnaire): void
    {
        $data = $questionnaire->mappedData();
        $inn = $data['inn'] ?? null;

        if (empty($inn)) {
            throw new QuestionnaireProcessingException('INN is required for registry check.');
        }

        // Placeholder for external API call.
        // In production replace this with a real HTTP client request.
        $externalStatus = $this->checkInnExternal((string) $inn);

        if ($externalStatus !== 'active') {
            throw new QuestionnaireProcessingException(
                "External registry check failed for INN {$inn}: status {$externalStatus}"
            );
        }

        $originalData = $questionnaire->data;
        $originalData['registry_status'] = $externalStatus;
        $questionnaire->data = $originalData;
    }

    private function checkInnExternal(string $inn): string
    {
        // TODO: replace with real external service call.
        return 'active';
    }
}
