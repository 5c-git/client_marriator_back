<?php

namespace App\Services\UserExternalSync;

use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Fields\Fields;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserExternalSyncService
{
    private ?Collection $fields = null;

    /**
     * Load active fields lazily so the service works correctly in tests
     * and long-running processes where fields may change.
     */
    private function getFields(): Collection
    {
        if ($this->fields === null) {
            $this->fields = Fields::where('active', true)->get()->keyBy('uuid');
        }

        return $this->fields;
    }

    /**
     * Force reload of fields from database. Useful in tests and queue workers.
     */
    public function refreshFields(): void
    {
        $this->fields = null;
    }

    /**
     * Build a structured payload for external system from user data.
     *
     * Base User model fields are exposed as key-value pairs.
     * Registration form fields are grouped under "registration_fields"
     * with UUID keys. Each field contains name, type and resolved value.
     */
    public function buildExportData(User $user): array
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

        return array_merge($baseFields, [
            'registration_fields' => $registrationFields,
        ]);
    }

    /**
     * Placeholder for sending user data to external system.
     * Replace with real HTTP client / API call when destination is known.
     */
    public function sendToExternalSystem(User $user): array
    {
        $payload = $this->buildExportData($user);

        Log::channel('single')->info('UserExternalSync: send payload', [
            'user_id' => $user->id,
            'payload' => $payload,
        ]);

        // TODO: replace with real external API call
        return [
            'status' => 'success',
            'message' => 'Payload prepared for external system',
            'user_id' => $user->id,
        ];
    }

    /**
     * Update user registration data from external system payload.
     * Payload must follow the same structure produced by buildExportData().
     */
    public function updateFromExternalSystem(User $user, array $payload): void
    {
        $registrationFields = $payload['registration_fields'] ?? [];

        if ($registrationFields === []) {
            return;
        }

        $currentData = $this->flattenUserData($user);
        $updatedData = [];

        foreach ($registrationFields as $fieldUuid => $fieldPayload) {
            $field = $this->getFields()->get($fieldUuid);

            if ($field === null) {
                continue;
            }

            $value = $fieldPayload['value'] ?? null;
            $updatedData[$fieldUuid] = $this->normalizeIncomingValue($field, $value);
        }

        $mergedData = array_merge($currentData, $updatedData);
        $user->data = $this->preserveDataFormat($user, $mergedData);
        $user->save();
    }

    /**
     * Convert user.data JSON to flat key-value array regardless of
     * pre- or post-registration format.
     */
    private function flattenUserData(User $user): array
    {
        $data = $user->data;

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (!is_array($data)) {
            return [];
        }

        $flat = [];
        $isStepBased = $this->isStepBasedData($data);

        if ($isStepBased) {
            foreach ($data as $stepFields) {
                if (is_array($stepFields)) {
                    $flat = array_merge($flat, $stepFields);
                }
            }
        } else {
            $flat = $data;
        }

        return $flat;
    }

    /**
     * Detect whether stored data is grouped by step (registration in progress)
     * or already flattened (registration completed).
     */
    private function isStepBasedData(array $data): bool
    {
        if ($data === []) {
            return false;
        }

        $firstKey = array_key_first($data);

        return is_numeric($firstKey) && is_array($data[$firstKey] ?? null);
    }

    /**
     * Preserve original data format: step-based for users still registering,
     * flat for users who finished registration.
     */
    private function preserveDataFormat(User $user, array $flatData): string
    {
        $originalData = $user->data;

        if (is_string($originalData)) {
            $originalData = json_decode($originalData, true) ?? [];
        }

        if ($this->isStepBasedData($originalData)) {
            $stepBased = [];

            foreach ($flatData as $fieldUuid => $value) {
                $field = $this->getFields()->get($fieldUuid);
                $step = $field?->step ?? 0;
                $stepBased[$step][$fieldUuid] = $value;
            }

            return json_encode($stepBased);
        }

        return json_encode($flatData);
    }

    /**
     * Extract basic user fields as scalar key-value pairs.
     */
    private function extractBaseFields(User $user): array
    {
        $baseFieldNames = [
            'id',
            'name',
            'email',
            'phone',
            'img',
            'confirmRegister',
            'finishRegister',
            'pin',
            'uuid',
            'latitude',
            'longitude',
            'archive',
            'verme_id',
            'nopaper_guid',
            'nopaper_certificate_id',
            'time_book_guid',
        ];

        $result = [];

        foreach ($baseFieldNames as $field) {
            $result[$field] = $user->$field;
        }

        return $result;
    }

    /**
     * Resolve a machine-readable type name for a field.
     */
    private function resolveFieldType(Fields $field, mixed $value): string
    {
        if (!empty($field->directory)) {
            return is_array($value) ? 'directory_multiple' : 'directory';
        }

        $enum = FieldsTypeEnum::tryFrom($field->type);

        if ($enum === null) {
            return 'unknown';
        }

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
        };
    }

    /**
     * Resolve a field value to external-system friendly structure.
     * Directory values are converted to [uuid, name] arrays.
     */
    private function resolveFieldValue(Fields $field, mixed $value): mixed
    {
        if (!empty($field->directory) && class_exists($field->directory)) {
            return $this->resolveDirectoryValue($field->directory, $value);
        }

        if (is_array($value)) {
            return array_map(fn ($item) => is_string($item) || is_numeric($item) ? (string) $item : $item, $value);
        }

        return $value;
    }

    /**
     * Resolve directory UUID(s) to [uuid, name] structure(s).
     */
    private function resolveDirectoryValue(string $directoryClass, mixed $value): mixed
    {
        if (!class_exists($directoryClass)) {
            return $value;
        }

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

    /**
     * Convert incoming external value back to storage format.
     * For directory values extract only UUID(s).
     */
    private function normalizeIncomingValue(Fields $field, mixed $value): mixed
    {
        if (!empty($field->directory)) {
            if (is_array($value)) {
                $isListOfOptions = array_is_list($value);

                if ($isListOfOptions) {
                    return array_map(fn ($item) => is_array($item) ? ($item['uuid'] ?? $item) : $item, $value);
                }

                return $value['uuid'] ?? $value;
            }

            return $value;
        }

        return $value;
    }
}
