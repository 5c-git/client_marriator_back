<?php

namespace Tests\Feature\Modules\Questionnaire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Questionnaire\Jobs\ProcessQuestionnaireStepJob;
use Modules\Questionnaire\Models\Questionnaire;
use Tests\TestCase;

class QuestionnaireObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_is_created_when_user_finishes_registration(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'finishRegister' => false,
            'confirmRegister' => false,
        ]);

        $user->finishRegister = true;
        $user->confirmRegister = true;
        $user->save();

        $this->assertDatabaseHas('questionnaires', [
            'user_id' => $user->id,
        ]);

        Queue::assertPushed(ProcessQuestionnaireStepJob::class);
    }

    public function test_questionnaire_is_not_created_without_finish_register(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'finishRegister' => false,
            'confirmRegister' => false,
        ]);

        $user->name = 'New Name';
        $user->save();

        $this->assertDatabaseMissing('questionnaires', [
            'user_id' => $user->id,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_existing_questionnaire_is_updated_on_re_register(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'phone' => '79269453055',
            'finishRegister' => false,
            'confirmRegister' => false,
        ]);

        $user->finishRegister = true;
        $user->confirmRegister = true;
        $user->save();

        $firstQuestionnaire = Questionnaire::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($firstQuestionnaire);

        $user->phone = '79112223344';
        $user->save();

        $secondQuestionnaire = Questionnaire::query()->where('user_id', $user->id)->first();
        $this->assertSame($firstQuestionnaire->id, $secondQuestionnaire->id);
        $this->assertSame('79112223344', $secondQuestionnaire->data['phone']);
    }
}
