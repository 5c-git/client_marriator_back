<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
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
            'placeId' => 'required|exists:directory_place,id',
            'selfEmployed' => 'required|boolean',

            'viewActivities' => 'required|array|min:1',
            'viewActivities.*.viewActivityId' => 'required|exists:directory_view_activities,id',
            'viewActivities.*.count' => 'required|integer|min:1',
            'viewActivities.*.dateStart' => 'required|date|after:now',
            'viewActivities.*.dateEnd' => 'required|date|after:viewActivities.*.dateStart',
            'viewActivities.*.needFoto' => 'required|boolean',

            'view_activities.*.dateActivity' => 'sometimes|array|min:1',
            'view_activities.*.dateActivity.*.timeStart' => 'required|date|after:now',
            'view_activities.*.dateActivity.*.timeEnd' => 'required|date|after:timeStart',
            'view_activities.*.dateActivity.*.placeIds' => 'required|array|min:1',
            'view_activities.*.dateActivity.*.placeIds.*' => [
                'required',
                Rule::exists('places', 'id'),
            ],
            'orderId' => [
                'sometimes',
                'integer',
                function ($attribute, $value, $fail) {
                    $orderExists = Order::query()->where('id', $value)
                        ->where('user_id', auth()->id())
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your order');
                    }
                },
            ],
        ];
    }
}
