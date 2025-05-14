<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;

/**
 * @property-read int placeId
 */
class SetPlaceRequest extends FormRequest
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
            'placeId' => 'required|integer|exists:directory_place,id',
        ];
    }
}
