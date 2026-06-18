<?php

namespace Tests\Feature\Services;

use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\UserExternalSync\UserExternalSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExternalSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserExternalSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserExternalSyncService();
    }

    public function test_build_export_data_contains_base_fields(): void
    {
        $user = User::factory()->create([
            'phone' => '79999999999',
            'finishRegister' => true,
            'confirmRegister' => true,
        ]);

        $data = $this->service->buildExportData($user);

        $this->assertSame($user->id, $data['id']);
        $this->assertSame('79999999999', $data['phone']);
        $this->assertArrayHasKey('registration_fields', $data);
    }

    public function test_build_export_data_resolves_directory_value(): void
    {
        $directoryValue = TaxStatus::create([
            'uuid' => 'nalogstatus_fiz_lico',
            'name' => 'Физическое лицо',
            'active' => true,
        ]);

        $field = Fields::create([
            'uuid' => 'nalogstatus',
            'name' => 'Налоговый статус',
            'type' => FieldsTypeEnum::directory->value,
            'directory' => TaxStatus::class,
            'active' => true,
            'step' => 2,
            'sort' => 1,
            'required' => true,
        ]);

        $user = User::factory()->create([
            'data' => json_encode([
                $field->uuid => $directoryValue->uuid,
            ]),
            'finishRegister' => true,
            'confirmRegister' => true,
        ]);

        $data = $this->service->buildExportData($user);
        $fieldData = $data['registration_fields'][$field->uuid];

        $this->assertSame('Налоговый статус', $fieldData['name']);
        $this->assertSame('directory', $fieldData['type']);
        $this->assertSame([
            'uuid' => 'nalogstatus_fiz_lico',
            'name' => 'Физическое лицо',
        ], $fieldData['value']);
    }

    public function test_build_export_data_flattens_step_based_data(): void
    {
        $field = Fields::create([
            'uuid' => 'fullname',
            'name' => 'ФИО',
            'type' => FieldsTypeEnum::text->value,
            'active' => true,
            'step' => 1,
            'sort' => 1,
            'required' => true,
        ]);

        $user = User::factory()->create([
            'data' => json_encode([
                '1' => [
                    $field->uuid => 'Иванов Иван',
                ],
            ]),
        ]);

        $data = $this->service->buildExportData($user);

        $this->assertSame('Иванов Иван', $data['registration_fields'][$field->uuid]['value']);
    }

    public function test_update_from_external_system_changes_user_data(): void
    {
        $directoryValue = TaxStatus::create([
            'uuid' => 'nalogstatus_fiz_lico',
            'name' => 'Физическое лицо',
            'active' => true,
        ]);

        $field = Fields::create([
            'uuid' => 'nalogstatus',
            'name' => 'Налоговый статус',
            'type' => FieldsTypeEnum::directory->value,
            'directory' => TaxStatus::class,
            'active' => true,
            'step' => 2,
            'sort' => 1,
            'required' => true,
        ]);

        $user = User::factory()->create([
            'data' => json_encode([]),
            'finishRegister' => true,
            'confirmRegister' => true,
        ]);

        $this->service->updateFromExternalSystem($user, [
            'registration_fields' => [
                $field->uuid => [
                    'name' => 'Налоговый статус',
                    'type' => 'directory',
                    'value' => [
                        'uuid' => $directoryValue->uuid,
                        'name' => $directoryValue->name,
                    ],
                ],
            ],
        ]);

        $user->refresh();
        $storedData = json_decode($user->data, true);

        $this->assertSame($directoryValue->uuid, $storedData[$field->uuid]);
    }

    public function test_update_from_external_system_preserves_step_format(): void
    {
        $field = Fields::create([
            'uuid' => 'fullname',
            'name' => 'ФИО',
            'type' => FieldsTypeEnum::text->value,
            'active' => true,
            'step' => 1,
            'sort' => 1,
            'required' => true,
        ]);

        $user = User::factory()->create([
            'data' => json_encode([
                '1' => [],
            ]),
        ]);

        $this->service->updateFromExternalSystem($user, [
            'registration_fields' => [
                $field->uuid => [
                    'name' => 'ФИО',
                    'type' => 'text',
                    'value' => 'Петров Петр',
                ],
            ],
        ]);

        $user->refresh();
        $storedData = json_decode($user->data, true);

        $this->assertSame('Петров Петр', $storedData['1'][$field->uuid]);
    }
}
