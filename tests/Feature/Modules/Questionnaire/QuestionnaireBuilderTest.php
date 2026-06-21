<?php

namespace Tests\Feature\Modules\Questionnaire;

use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Fields;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Questionnaire\Enums\QuestionnaireStatus;
use Modules\Questionnaire\Services\QuestionnaireBuilder;
use Tests\TestCase;

class QuestionnaireBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_questionnaire_for_user(): void
    {
        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode([
                'full_name' => 'Иванов Иван',
            ]),
        ]);

        $builder = new QuestionnaireBuilder();
        $questionnaire = $builder->buildForUser($user);

        $this->assertSame($user->id, $questionnaire->user_id);
        $this->assertSame(QuestionnaireStatus::PENDING->value, $questionnaire->status);
        $this->assertSame('79269453055', $questionnaire->data['phone']);
        $this->assertSame('Иванов Иван', $questionnaire->data['full_name']);
    }

    public function test_updates_existing_questionnaire_instead_of_creating_new(): void
    {
        $user = User::factory()->create([
            'phone' => '79269453055',
            'data' => json_encode(['full_name' => 'Иванов Иван']),
        ]);

        $builder = new QuestionnaireBuilder();
        $first = $builder->buildForUser($user);

        $user->phone = '79112223344';
        $user->save();

        $second = $builder->buildForUser($user);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(QuestionnaireStatus::PENDING->value, $second->status);
        $this->assertSame('79112223344', $second->data['phone']);
    }

    public function test_includes_additional_user_fields_and_stores_descriptive_data_in_separate_columns(): void
    {
        $expansionData = [
            'field-uuid' => [
                ['name' => 'ФИО', 'value' => 'Тест'],
            ],
        ];
        $errorData = ['field-uuid' => 'Ошибка заполнения'];
        $requisitesData = [
            ['bik' => '044525600', 'fio' => 'test', 'card' => '5536913757516790'],
        ];

        $user = User::factory()->create([
            'mapAddress' => 'г. Москва, Красная площадь',
            'mapRadius' => 5000,
            'expansionData' => json_encode($expansionData),
            'errorData' => json_encode($errorData),
            'requisitesData' => json_encode($requisitesData),
        ]);

        $builder = new QuestionnaireBuilder();
        $questionnaire = $builder->buildForUser($user);

        $this->assertSame('г. Москва, Красная площадь', $questionnaire->data['mapAddress']);
        $this->assertSame(5000, $questionnaire->data['mapRadius']);
        $this->assertSame($expansionData, $questionnaire->expansion_data);
        $this->assertSame($errorData, $questionnaire->error_data);
        $this->assertSame($requisitesData, $questionnaire->requisites_data);
        $this->assertArrayNotHasKey('expansionData', $questionnaire->data);
        $this->assertArrayNotHasKey('errorData', $questionnaire->data);
        $this->assertArrayNotHasKey('requisitesData', $questionnaire->data);
        $this->assertArrayNotHasKey('archive', $questionnaire->data);
    }

    public function test_resolves_directory_field_values(): void
    {
        TaxStatus::create([
            'uuid' => 'nalogstatus_fiz_lico',
            'name' => 'Физическое лицо',
            'active' => true,
        ]);

        Fields::create([
            'uuid' => 'nalogstatus',
            'name' => 'Налоговый статус',
            'type' => 8, // directory
            'directory' => TaxStatus::class,
            'active' => true,
            'step' => 2,
            'sort' => 1,
            'required' => true,
        ]);

        $user = User::factory()->create([
            'data' => json_encode([
                'nalogstatus' => 'nalogstatus_fiz_lico',
            ]),
        ]);

        $builder = new QuestionnaireBuilder();
        $questionnaire = $builder->buildForUser($user);

        $field = $questionnaire->data['registration_fields']['nalogstatus'];

        $this->assertSame('Налоговый статус', $field['name']);
        $this->assertSame('directory', $field['type']);
        $this->assertSame([
            'uuid' => 'nalogstatus_fiz_lico',
            'name' => 'Физическое лицо',
        ], $field['value']);
    }
}
