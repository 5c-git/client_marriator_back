<?php

namespace Modules\Questionnaire\Services;

use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Fields\Fields;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Questionnaire\Enums\QuestionnaireStatus;
use Modules\Questionnaire\Models\Questionnaire;

class QuestionnaireBuilder
{
    private ?Collection $fields = null;

    /**
     * Build or update a questionnaire for the given user and start processing.
     */
    public function buildForUser(User $user): Questionnaire
    {
        $data = $this->buildData($user);

        $questionnaire = Questionnaire::query()->where('user_id', $user->id)->first();

        if ($questionnaire === null) {
            $questionnaire = new Questionnaire();
            $questionnaire->user_id = $user->id;
        }

        $questionnaire->status = QuestionnaireStatus::PENDING->value;
        $questionnaire->current_step_index = null;
        $questionnaire->current_step_class = null;
        $questionnaire->error_message = null;
        $questionnaire->completed_at = null;
        $questionnaire->failed_at = null;
        $questionnaire->logs = [];
        $questionnaire->data = $data;
        $questionnaire->save();

        return $questionnaire;
    }

    /**
     * Convert user data into questionnaire storage format.
     */
    private function buildData(User $user): array
    {
        $flatData = $this->flattenUserData($user);

        $baseFields = $this->extractBaseFields($user);
        $registrationFields = [];

        foreach ($flatData as $fieldUuid => $value) {
            $field = $this->getFields()->get($fieldUuid);

            if ($field === null) {
                continue;
            }

            $registrationFields[$fieldUuid] = [
                'name' => $field->name,
                'type' => $this->resolveFieldType($field, $value),
                'value' => $this->resolveFieldValue($field, $value),
            ];
        }

        return array_merge($baseFields, $flatData, [
            'registration_fields' => $registrationFields,
        ]);
    }

    private function flattenUserData(User $user): array
    {
        $data = $user->data;

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (! is_array($data)) {
            return [];
        }

        $isStepBased = $data !== [] && is_numeric(array_key_first($data)) && is_array(reset($data));

        if (! $isStepBased) {
            return $data;
        }

        $flat = [];
        foreach ($data as $stepFields) {
            if (is_array($stepFields)) {
                $flat = array_merge($flat, $stepFields);
            }
        }

        return $flat;
    }

    private function extractBaseFields(User $user): array
    {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'img' => $user->img,
            'finishRegister' => $user->finishRegister,
            'confirmRegister' => $user->confirmRegister,
            'pin' => $user->pin,
            'uuid' => $user->uuid,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'archive' => $user->archive,
        ];
    }

    private function resolveFieldType(Fields $field, mixed $value): string
    {
        if (! empty($field->directory)) {
            return is_array($value) ? 'directory_multiple' : 'directory';
        }

        $enum = FieldsTypeEnum::tryFrom($field->type);

        return match ($enum) {
            FieldsTypeEnum::checkbox => 'checkbox',
            FieldsTypeEnum::checkboxMultiple => 'checkbox_multiple',
            FieldsTypeEnum::file => 'file',
            FieldsTypeEnum::photoCheckbox => 'photo_checkbox',
            FieldsTypeEnum::radio => 'radio',
            FieldsTypeEnum::select => 'select',
            FieldsTypeEnum::text => 'text',
            FieldsTypeEnum::account => 'account',
            FieldsTypeEnum::card => 'card',
            FieldsTypeEnum::date => 'date',
            FieldsTypeEnum::email => 'email',
            FieldsTypeEnum::inn => 'inn',
            FieldsTypeEnum::month => 'month',
            FieldsTypeEnum::phone => 'phone',
            FieldsTypeEnum::sms => 'sms',
            FieldsTypeEnum::snils => 'snils',
            FieldsTypeEnum::photo => 'photo',
            FieldsTypeEnum::autocomplete => 'autocomplete',
            FieldsTypeEnum::selectMultiple => 'select_multiple',
            FieldsTypeEnum::bic => 'bic',
            FieldsTypeEnum::directory => 'directory',
            default => 'unknown',
        };
    }

    private function resolveFieldValue(Fields $field, mixed $value): mixed
    {
        if (! empty($field->directory) && class_exists($field->directory)) {
            return $this->resolveDirectoryValue($field->directory, $value);
        }

        return $value;
    }

    private function resolveDirectoryValue(string $directoryClass, mixed $value): mixed
    {
        $items = $directoryClass::getAllData()->keyBy('uuid');

        if (is_array($value)) {
            return array_values(array_filter(array_map(function ($uuid) use ($items) {
                $item = $items->get($uuid);

                return $item ? ['uuid' => $uuid, 'name' => $item->name] : null;
            }, $value)));
        }

        $item = $items->get($value);

        return $item ? ['uuid' => $value, 'name' => $item->name] : ['uuid' => $value, 'name' => 'NOT FOUND'];
    }

    private function getFields(): Collection
    {
        if ($this->fields === null) {
            $this->fields = Fields::where('active', true)->get()->keyBy('uuid');
        }

        return $this->fields;
    }
}
