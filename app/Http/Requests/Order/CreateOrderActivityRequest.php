<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use Illuminate\Validation\Rule;

class CreateOrderActivityRequest extends FormRequest
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
            'viewActivityId' => 'required|exists:directory_view_activities,id',
            'count' => 'required|integer|min:1',
            'dateStart' => 'required|date|after:now',
            'dateEnd' => 'required|date|after:dateStart',
            'needFoto' => 'required|boolean',

            'dateActivity' => 'sometimes|array|min:1',
            'dateActivity.*.timeStart' => 'required|date|after:now',
            'dateActivity.*.timeEnd' => 'required|date|after:timeStart',
            'dateActivity.*.placeIds' => 'sometimes|array|min:1',
            'dateActivity.*.placeIds.*' => [
                'sometimes',
                Rule::exists('directory_place', 'id'),
            ],
            'orderId' => [
                'required',
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
