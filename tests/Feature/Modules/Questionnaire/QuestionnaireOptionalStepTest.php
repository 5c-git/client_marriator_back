<?php

namespace Tests\Feature\Modules\Questionnaire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Questionnaire\Enums\QuestionnaireFieldAlias;
use Modules\Questionnaire\Enums\QuestionnaireStatus;
use Modules\Questionnaire\Services\QuestionnaireProcessor;
use Tests\Feature\Modules\Questionnaire\Fixtures\OptionalFailingStep;
use Tests\TestCase;

class QuestionnaireOptionalStepTest extends TestCase
{
    use RefreshDatabase;

    public function test_optional_failure_continues_to_next_step(): void
    {
        config(['queue.default' => 'sync']);
        config(['questionnaire.steps' => [
            \Modules\Questionnaire\Services\Steps\ValidatePersonalDataStep::class,
            OptionalFailingStep::class,
            \Modules\Questionnaire\Services\Steps\EnrichGeoDataStep::class,
        ]]);

        $user = User::factory()->create([
            'phone' => '79152142630',
            'data' => json_encode([
                QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
                QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
                QuestionnaireFieldAlias::PHONE->uuid() => '79152142630',
            ]),
        ]);

        $processor = app(QuestionnaireProcessor::class);
        $questionnaire = $processor->processUser($user);
        $questionnaire->refresh();

        $this->assertSame(QuestionnaireStatus::COMPLETED->value, $questionnaire->status);

        $failedLog = collect($questionnaire->logs)->firstWhere('status', 'failed');
        $this->assertNotNull($failedLog);
        $this->assertFalse($failedLog['required']);

        $successLogs = collect($questionnaire->logs)->where('status', 'success');
        $this->assertCount(2, $successLogs);
    }

    public function test_required_failure_stops_the_chain(): void
    {
        config(['queue.default' => 'sync']);

        $user = User::factory()->create([
            'phone' => '123',
            'data' => json_encode([
                QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
                QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
                QuestionnaireFieldAlias::PHONE->uuid() => '123',
            ]),
        ]);

        $processor = app(QuestionnaireProcessor::class);
        $questionnaire = $processor->processUser($user);
        $questionnaire->refresh();

        $this->assertSame(QuestionnaireStatus::FAILED->value, $questionnaire->status);
        $this->assertSame(0, $questionnaire->current_step_index);
    }
}
