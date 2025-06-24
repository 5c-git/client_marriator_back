<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Request;
use Illuminate\Validation\Rule;

/**
 * @property-read int requestId
 */
class AcceptRequestRequest extends FormRequest
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
            'requestId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userPlaceId = $user->place?->pluck('id')?->toArray();
                    $orderExists = Request::query()->where('id', $value)
                        ->where('status', OrderStatusEnum::notAccepted)
                        ->whereIn('place_id', $userPlaceId)
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your task');
                    }
                },
            ],
        ];
    }
}
