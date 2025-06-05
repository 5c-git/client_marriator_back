<?php

namespace App\Http\Requests;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;
use App\Enum\User\UserStatusModerationEnum;
use App\Enum\Role\RoleEnum;

/**
 * @property-read int status
 * @property-read int page
 * @property-read int perPage
 */
class PaginatorRequest extends FormRequest
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
            'status'  => [
                'sometimes',
                'integer',
                Rule::in([
                    UserStatusModerationEnum::new->value,
                    UserStatusModerationEnum::inProgress->value,
                    UserStatusModerationEnum::archive->value,
                ]),
            ],
            'role'  => [
                'sometimes',
                'integer',
                Rule::in([
                    RoleEnum::admin->value,
                    RoleEnum::client->value,
                    RoleEnum::manager->value,
                    RoleEnum::recruiter->value,
                    RoleEnum::specialist->value,
                    RoleEnum::supervisor->value,
                ]),
            ],
            'page' => 'sometimes|integer',
            'perPage' => 'sometimes|integer',
        ];
    }
}
