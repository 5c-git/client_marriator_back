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
                'exists:users,id',
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
    }
}
