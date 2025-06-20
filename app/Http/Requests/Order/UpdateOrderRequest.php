<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
            'placeId' => [
                'required',
                'integer',
                'exists:directory_place,id',
                function ($attribute, $value, $fail) {
                    $places = Auth::user()->project
                        ->flatMap(fn($project) => $project->places)
                        ->unique('id')?->pluck('id')?->toArray();

                    if (!in_array($value,$places)) {
                        $fail('Not your place');
                    }
                },
            ],
            'selfEmployed' => 'required|boolean',
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
