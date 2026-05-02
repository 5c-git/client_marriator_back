<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\User;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RepeatOrderRequest extends FormRequest
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
                    $users = User::where('id',Auth::user()->id)
                        ->whereHas('project')
                        ->whereDoesntHave('project', function ($query) {
                            $query->where('date_end', '>', Carbon::now());
                        })
                        ->first();
                    if($users){
                        $fail('User project is out of date');
                        return;
                    }
                    $orderExists = Order::query()->where('id', $value)
                        ->where('user_id', auth()->id())
                        ->where('status',OrderStatusEnum::canceled->value)
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
                        if (TimeService::getTimeDifferenceSub(
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
