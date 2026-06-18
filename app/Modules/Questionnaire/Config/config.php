<?php

use Modules\Questionnaire\Services\Steps\CheckExternalRegistryStep;
use Modules\Questionnaire\Services\Steps\EnrichGeoDataStep;
use Modules\Questionnaire\Services\Steps\ValidatePersonalDataStep;

return [
    /*
    |--------------------------------------------------------------------------
    | Questionnaire Processing Steps
    |--------------------------------------------------------------------------
    |
    | Each class must implement Modules\Questionnaire\Services\Steps\QuestionnaireStepInterface.
    | Steps are executed sequentially in the order defined here.
    |
    */
    'steps' => [
        ValidatePersonalDataStep::class,
        CheckExternalRegistryStep::class,
        EnrichGeoDataStep::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue used for questionnaire processing jobs.
    |
    */
    'queue' => env('QUESTIONNAIRE_QUEUE', 'questionnaire'),
];
