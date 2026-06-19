<?php

namespace Tests\Feature\Modules\Questionnaire\Fixtures;

use Modules\Questionnaire\Exceptions\QuestionnaireProcessingException;
use Modules\Questionnaire\Models\Questionnaire;
use Modules\Questionnaire\Services\Steps\QuestionnaireStepInterface;

class OptionalFailingStep implements QuestionnaireStepInterface
{
    public function name(): string
    {
        return 'fixture.optional_failing';
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function handle(Questionnaire $questionnaire): void
    {
        throw new QuestionnaireProcessingException('Optional step failed');
    }
}
