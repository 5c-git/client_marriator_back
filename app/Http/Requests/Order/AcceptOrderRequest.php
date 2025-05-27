<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read int orderId
 */
class AcceptOrderRequest extends FormRequest
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
            'orderId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $place = $user->place?->pluck('id')->toArray();

                    $orderExists = Order::query()
                        ->where('id', $value)
                        ->where('status', [OrderStatusEnum::notAccepted])
                        ->whereIn('place_id', $place)
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your order');
                    }
                },
            ],
        ];
    }
}
