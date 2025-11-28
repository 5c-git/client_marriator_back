<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\OrderActivities;
use App\Services\TimeService;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;

/**
 * @property-read int orderId
 */
class OrderByIdCancelRequest extends FormRequest
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
                    $orderExists = Order::query()->where('id', $value)
                        ->where('user_id', auth()->id())
                        ->whereIn('status', [
                            OrderStatusEnum::new->value,
                            OrderStatusEnum::notAccepted->value
                        ])
                        ->first();

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }
                    /** @var Order $orderExists */
                    /** @var OrderActivities $orderActivities */
                    $orderActivities = $orderExists->orderActivities()
                        ->orderBy('date_start')
                        ->first();

                    if($orderActivities) {
                        if (!TimeService::getTimeDifferenceSub(
                            $this->user(),
                            'cancel_order',
                            $orderActivities?->date_start
                        )) {
                            $fail('Order activities time start is arrived');
                        }
                    }

                },
            ],
        ];
    }
}
