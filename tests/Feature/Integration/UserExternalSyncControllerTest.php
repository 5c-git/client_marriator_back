<?php

namespace Tests\Feature\Integration;

use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Fields;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExternalSyncControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.one_c.token' => 'test-integration-token-with-at-least-forty-characters-long']);
    }

    public function test_sync_user_requires_integration_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/integration/syncUser/', [
            'user_id' => $user->id,
            'registration_fields' => [],
        ]);

        $response->assertStatus(403);
    }

    public function test_sync_user_with_valid_token_updates_data(): void
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

        $response = $this->postJson('/api/integration/syncUser/', [
            'user_id' => $user->id,
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
        ], [
            'Authorization' => 'Bearer test-integration-token-with-at-least-forty-characters-long',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user_id' => $user->id,
            ]);

        $user->refresh();
        $storedData = json_decode($user->data, true);

        $this->assertSame($directoryValue->uuid, $storedData[$field->uuid]);
    }

    public function test_sync_user_returns_validation_error_for_missing_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/integration/syncUser/', [
            'user_id' => $user->id,
        ], [
            'Authorization' => 'Bearer test-integration-token-with-at-least-forty-characters-long',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registration_fields']);
    }

    public function test_sync_user_returns_validation_error_for_unknown_user(): void
    {
        $response = $this->postJson('/api/integration/syncUser/', [
            'user_id' => 999999,
            'registration_fields' => ['dummy'],
        ], [
            'Authorization' => 'Bearer test-integration-token-with-at-least-forty-characters-long',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }
}
