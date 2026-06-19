<?php

namespace Modules\Questionnaire\Services\Steps;

use Modules\Questionnaire\Exceptions\QuestionnaireProcessingException;
use Modules\Questionnaire\Models\Questionnaire;

class ValidatePersonalDataStep implements QuestionnaireStepInterface
{
    public function name(): string
    {
        return 'validation.personal_data';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function handle(Questionnaire $questionnaire): void
    {
        $data = $questionnaire->mappedData();

        $required = ['full_name', 'phone'];

        foreach ($required as $field) {
            if (empty($data[$field] ?? null)) {
                throw new QuestionnaireProcessingException(
                    "Missing required field: {$field}"
                );
            }
        }

        $phone = $data['phone'];

        if (! preg_match('/^7\d{10}$/', (string) $phone)) {
            throw new QuestionnaireProcessingException(
                'Invalid phone format. Expected 11 digits starting with 7.'
            );
        }

        // Store enrichment on the original data array.
        $originalData = $questionnaire->data;
        $originalData['phone_normalized'] = $phone;
        $questionnaire->data = $originalData;
    }
}
