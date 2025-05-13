<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;

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
            'confirm' => 'required|boolean'
        ];
    }
}
