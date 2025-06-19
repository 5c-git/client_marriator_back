<?php

namespace App\Http\Requests\UserData;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @property-read int userId
 * @property-read int projectId
 */
class SetUserImgRequest extends FormRequest
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
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value == Auth::id()) {
                        $fail('Not use your id');
                    }
                }
            ],
            'projectId' => 'required|integer|exists:directory_project,id',
        ];
    }
}
