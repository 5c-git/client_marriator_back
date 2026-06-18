<?php

namespace Modules\Questionnaire\Services\Steps;

use Modules\Questionnaire\Models\Questionnaire;

interface QuestionnaireStepInterface
{
    /**
     * Human-readable step name.
     */
    public function name(): string;

    /**
     * Execute the step against the questionnaire.
     *
     * @throws \Modules\Questionnaire\Exceptions\QuestionnaireProcessingException
     */
    public function handle(Questionnaire $questionnaire): void;
}
