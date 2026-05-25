<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GetProjectForOrderRequest extends FormRequest
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
                                OrderStatusEnum::notAccepted->value,
                            ]
                        )
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }

                    $users = User::where('id',Auth::user()->id)
                        ->whereHas('project')
                        ->whereDoesntHave('project', function ($query) {
                            $query->where('date_end', '>', Carbon::now());
                        })
                        ->first();
                    if($users){
                        $fail('User project is out of date');
                    }
                },
            ],
        ];
    }
}
