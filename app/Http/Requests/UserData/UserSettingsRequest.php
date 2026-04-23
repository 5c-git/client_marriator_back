<?php

namespace App\Http\Requests\UserData;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @property-read bool notificationNewBids
 */
class UserSettingsRequest extends FormRequest
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
            'notificationNewBids' => 'sometimes|boolean',
        ];
    }
}
