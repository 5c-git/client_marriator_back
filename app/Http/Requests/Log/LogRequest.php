<?php

namespace App\Http\Requests\Log;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\User;

/**
 * @property-read int userId
 * @property-read string $requestUrl
 * @property-read string $requestStatus
 * @property-read string $requestBody
 * @property-read string $requestResponse
 */
class LogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $user = auth()->user();
        $this->userId = $user->id;

        return [
            'requestUrl' => 'required|string',
            'requestStatus' => 'required|integer',
            'requestBody' => 'required|array',
            'requestResponse' => 'required|string',
        ];
    }
}
