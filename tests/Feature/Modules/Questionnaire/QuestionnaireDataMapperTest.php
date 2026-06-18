<?php

namespace Tests\Feature\Modules\Questionnaire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Questionnaire\Enums\QuestionnaireFieldAlias;
use Modules\Questionnaire\Services\QuestionnaireDataMapper;
use Tests\TestCase;

class QuestionnaireDataMapperTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_uuid_keys_to_semantic_keys(): void
    {
        $mapper = new QuestionnaireDataMapper();

        $data = [
            QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
            QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
            QuestionnaireFieldAlias::PHONE->uuid() => '79152142630',
        ];

        $mapped = $mapper->map($data);

        $this->assertSame('Иван', $mapped['first_name']);
        $this->assertSame('Иванов', $mapped['last_name']);
        $this->assertSame('79152142630', $mapped['phone']);
    }

    public function test_builds_full_name_from_name_parts(): void
    {
        $mapper = new QuestionnaireDataMapper();

        $data = [
            QuestionnaireFieldAlias::FIRST_NAME->uuid() => 'Иван',
            QuestionnaireFieldAlias::LAST_NAME->uuid() => 'Иванов',
            QuestionnaireFieldAlias::PATRONYMIC->uuid() => 'Иванович',
        ];

        $mapped = $mapper->map($data);

        $this->assertSame('Иванов Иван Иванович', $mapped['full_name']);
    }

    public function test_full_name_is_null_when_name_parts_missing(): void
    {
        $mapper = new QuestionnaireDataMapper();

        $mapped = $mapper->map(['phone' => '79152142630']);

        $this->assertNull($mapped['full_name']);
    }

    public function test_original_data_is_preserved(): void
    {
        $mapper = new QuestionnaireDataMapper();

        $uuid = QuestionnaireFieldAlias::PHONE->uuid();
        $data = [$uuid => '79152142630'];

        $mapped = $mapper->map($data);

        $this->assertSame('79152142630', $mapped[$uuid]);
        $this->assertSame('79152142630', $mapped['phone']);
    }
}
