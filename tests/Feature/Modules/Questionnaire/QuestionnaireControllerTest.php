<?php

namespace Tests\Feature\Modules\Questionnaire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Questionnaire\Jobs\ProcessQuestionnaireStepJob;
use Tests\TestCase;

class QuestionnaireControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_endpoint_dispatches_questionnaire_job(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode(['full_name' => 'Иванов Иван']),
        ]);

        $response = $this->postJson(route('questionnaire.start', $user));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user_id', $user->id);

        Queue::assertPushed(ProcessQuestionnaireStepJob::class);
    }

    public function test_status_endpoint_returns_questionnaire_state(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode(['full_name' => 'Иванов Иван']),
        ]);

        $this->postJson(route('questionnaire.start', $user));

        $response = $this->getJson(route('questionnaire.status', $user));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('status_name', 'pending');
    }

    public function test_result_endpoint_returns_404_when_no_questionnaire(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('questionnaire.result', $user));

        $response->assertNotFound()
            ->assertJsonPath('message', 'Questionnaire not found');
    }

    public function test_result_endpoint_returns_data(): void
    {
        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode(['full_name' => 'Иванов Иван']),
        ]);

        $this->postJson(route('questionnaire.start', $user));

        $response = $this->getJson(route('questionnaire.result', $user));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonStructure(['data' => ['user_id', 'phone', 'registration_fields']]);
    }
}
