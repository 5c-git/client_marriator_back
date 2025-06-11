<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\User;

/**
 * @property-read int userId
 * @property-read bool confirm
 */
class ConfirmUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
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
            'change_order' => 'sometimes|date_format:H:i',
            'cancel_order' => 'sometimes|date_format:H:i',
            'live_order' => 'sometimes|date_format:H:i',
            'change_task' => 'sometimes|date_format:H:i',
            'cancel_task' => 'sometimes|date_format:H:i',
            'live_task' => 'sometimes|date_format:H:i',
            'repeat_bid' => 'sometimes|date_format:H:i',
            'leave_bid' => 'sometimes|date_format:H:i',
            'refusal_task' => 'sometimes|date_format:H:i',
            'waiting_task' => 'sometimes|integer|min:1',
            'confirm' => 'required|boolean',

        ];
    }
}
