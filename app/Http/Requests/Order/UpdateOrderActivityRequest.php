<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Services\TimeService;
use Illuminate\Validation\Rule;

class UpdateOrderActivityRequest extends FormRequest
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
            'viewActivityId' => 'sometimes|exists:directory_view_activities,id',
            'orderActivity' => 'required|exists:order_activities,id',
            'count' => 'sometimes|integer|min:1',
            'dateStart' => 'sometimes|date|after:now',
            'dateEnd' => 'sometimes|date|after:dateStart',
            'needFoto' => 'sometimes|boolean',

            'dateActivity' => 'sometimes|array|min:1',
            'dateActivity.*.timeStart' => 'required|date|after:now',
            'dateActivity.*.timeEnd' => 'required|date|after:timeStart',
            'dateActivity.*.placeIds' => 'sometimes|array|min:1',
            'dateActivity.*.placeIds.*' => [
                'required',
                Rule::exists('directory_place', 'id'),
            ],
            'orderId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $orderExists = Order::query()->where('id', $value)
                        ->where('user_id', auth()->id())
                        ->where('status', OrderStatusEnum::new->value)
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
                            'change_order',
                            $orderActivities?->date_start
                        )) {
                            $fail('Order activities time start is arrived after change order');
                        }
                    }
                },
            ],
        ];
    }
}
