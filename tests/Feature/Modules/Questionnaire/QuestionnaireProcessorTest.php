<?php

namespace Tests\Feature\Modules\Questionnaire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Questionnaire\Enums\QuestionnaireFieldAlias;
use Modules\Questionnaire\Enums\QuestionnaireStatus;
use Modules\Questionnaire\Jobs\ProcessQuestionnaireStepJob;
use Modules\Questionnaire\Services\QuestionnaireProcessor;
use Tests\TestCase;

class QuestionnaireProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_all_steps_successfully(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode([
                QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
                QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
                QuestionnaireFieldAlias::PHONE->uuid() => '79269453055',
                QuestionnaireFieldAlias::INN->uuid() => '123456789012',
                'latitude' => '55.7558',
                'longitude' => '37.6173',
            ]),
        ]);

        $processor = app(QuestionnaireProcessor::class);
        $questionnaire = $processor->processUser($user);

        $this->assertSame(QuestionnaireStatus::PENDING->value, $questionnaire->status);
        Queue::assertPushed(ProcessQuestionnaireStepJob::class, function ($job) use ($questionnaire) {
            return $job->questionnaireId() === $questionnaire->id && $job->stepIndex() === 0;
        });
    }

    public function test_full_chain_runs_synchronously_with_sync_queue(): void
    {
        config(['queue.default' => 'sync']);

        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode([
                QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
                QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
                QuestionnaireFieldAlias::PHONE->uuid() => '79269453055',
                QuestionnaireFieldAlias::INN->uuid() => '123456789012',
                'latitude' => '55.7558',
                'longitude' => '37.6173',
            ]),
        ]);

        $processor = app(QuestionnaireProcessor::class);
        $questionnaire = $processor->processUser($user);
        $questionnaire->refresh();

        $this->assertSame(QuestionnaireStatus::COMPLETED->value, $questionnaire->status);
        $this->assertSame('79269453055', $questionnaire->data['phone_normalized']);
        $this->assertSame('active', $questionnaire->data['registry_status']);
        $this->assertNotEmpty($questionnaire->logs);
    }

    public function test_chain_fails_on_invalid_phone(): void
    {
        config(['queue.default' => 'sync']);

        $user = User::factory()->create([
            'phone' => '123',
            'data' => json_encode([
                'full_name' => 'Иванов Иван',
                'phone' => '123',
            ]),
        ]);

        $processor = app(QuestionnaireProcessor::class);
        $questionnaire = $processor->processUser($user);
        $questionnaire->refresh();

        $this->assertSame(QuestionnaireStatus::FAILED->value, $questionnaire->status);
        $this->assertSame(0, $questionnaire->current_step_index);
        $this->assertSame(\Modules\Questionnaire\Services\Steps\ValidatePersonalDataStep::class, $questionnaire->current_step_class);
        $this->assertNotNull($questionnaire->error_message);
    }

    public function test_restart_clears_previous_state(): void
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

        $user->update(['phone' => '79269453055']);
        $questionnaire->data = array_merge(
            $questionnaire->data,
            [
                QuestionnaireFieldAlias::PHONE->uuid() => '79269453055',
                QuestionnaireFieldAlias::INN->uuid() => '123456789012',
            ]
        );
        $questionnaire->save();

        $processor->restart($questionnaire);
        $questionnaire->refresh();

        $this->assertSame(QuestionnaireStatus::COMPLETED->value, $questionnaire->status);
        $this->assertNull($questionnaire->error_message);
    }
}
