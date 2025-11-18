<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\OrderActivities;
use App\Services\TimeService;
use Carbon\Carbon;
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
                        ->where('status', OrderStatusEnum::notAccepted->value)
                        ->whereIn('place_id', $place)
                        ->first();

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }
                    /** @var Order $orderExists */
                    /** @var OrderActivities $orderActivities */
                    $orderActivities = $orderExists->orderActivities()
                        ->orderBy('date_end','desc')
                        ->first();


                    if($orderActivities) {
                        if (!$orderActivities->date_end->gt(Carbon::now())) {
                            $fail('Order activities time end is ended');
                        }
                    }
                },
            ],
        ];
    }
}
