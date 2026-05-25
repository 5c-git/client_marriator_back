<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\OrderActivities;
use App\Models\Order\TaskActivity;
use App\Models\User;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;

/**
 * @property-read int orderId
 */
class OrderByIdRequest extends FormRequest
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
                        ->first();

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

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }

                    $orderActivities = $orderExists->orderActivities()
                        ->orderBy('date_end','desc')
                        ->first();
                    if(!$orderActivities){
                        $fail('order activities not exist');
                        return;
                    }

                    /** @var OrderActivities $orderActivities */
                    if($orderActivities) {
                        if (!$orderActivities->date_end?->gt(Carbon::now())) {
                            $fail('Order activities time start is arrived after end of Activities');
                        }
                    }
                },
            ],
        ];
    }
}
