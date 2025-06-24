<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @property-read int userId
 * @property-read bool confirm
 */
class ConfirmUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'userId' => 'required|integer|exists:users,id',
            'supervisorIds' => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    $uniqueIds = array_unique($value);
                    if ($uniqueIds) {
                        $validIds = User::whereIn('id', $uniqueIds)
                            ->whereHas('roles', function ($query) {
                                $query->where('role_id', RoleEnum::supervisor);
                            })
                            ->pluck('id')
                            ->toArray();

                        $invalidIds = array_diff($uniqueIds, $validIds);

                        if (!empty($invalidIds)) {
                            $fail('Supervisor ids not exist ' . implode(', ', $invalidIds));
                        }
                    }
                }
            ],
            'confirm' => 'required|boolean',
        ];

        $timeFields = [
            'change_order', 'cancel_order', 'live_order',
            'change_task', 'cancel_task', 'live_task',
            'repeat_bid', 'leave_bid', 'refusal_task'
        ];

        foreach ($timeFields as $field) {
            $rules[$field] = [
                'sometimes',
                'date_format:H:i'
                ];
        }
        $rules['waiting_task'] = 'sometimes|integer|min:1';

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Если есть ошибки в userId - пропускаем
            if ($validator->errors()->has('userId')) {
                return;
            }

            $user = User::find($this->userId);
            $userRoles = $user->roles?->pluck('id')->toArray();

            if (in_array(RoleEnum::client->value,$userRoles)) {
                $timeFields = [
                    'change_order', 'cancel_order', 'live_order',
                ];
                foreach ($timeFields as $field) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add(
                            $field,
                            "Field $field required for user role"
                        );
                    }
                }
            }

            if (in_array(RoleEnum::manager->value,$userRoles)) {
                $timeFields = [
                    'change_task', 'cancel_task', 'live_task',
                    'repeat_bid', 'leave_bid'
                ];
                foreach ($timeFields as $field) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add(
                            $field,
                            "Field $field required for user role"
                        );
                    }
                }
            }

            if (in_array(RoleEnum::supervisor->value,$userRoles)) {
                $timeFields = [
                    'repeat_bid', 'leave_bid', 'refusal_task', 'waiting_task'
                ];
                foreach ($timeFields as $field) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add(
                            $field,
                            "Field $field required for user role"
                        );
                    }
                }
            }
        });
    }
}
