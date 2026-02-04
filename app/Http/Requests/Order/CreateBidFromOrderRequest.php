<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read int orderId
 * @property-read int orderActivityId
 */
class CreateBidFromOrderRequest extends FormRequest
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

                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Order::where(function ($query) use ($user,$value,$userIdsSupervisor) {
                        $query->whereIn('user_id', $userIdsSupervisor)->where('id', $value)
                            ->where('status', OrderStatusEnum::accepted->value);
                    })
                        ->orWhere(function ($query) use ($user,$value,$userIdsSupervisor) {
                            $query->whereIn('accept_user_id', $userIdsSupervisor)->where('id', $value)
                                ->where('status', OrderStatusEnum::accepted->value);
                        })->first();

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }
                    /** @var Order $orderExists */
                    $bids = $orderExists->bids?->where('activity_id', $this->orderActivityId)->sortByDesc('created_at')->first();
                    if ($bids) {
                        /** @var Bid $bids */
                        if(!TimeService::getTimeDifferenceAdd($this->user(),'repeat_bid',$bids->created_at)){
                            $fail('Time before date of create new bid');
                        }
                    }

                },
            ],
            'orderActivityId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $orderActivitiesIds = Order::where('id',$this->orderId)->first()?->orderActivities?->pluck('id')->toArray();
                    if (!$orderActivitiesIds || !in_array($value,$orderActivitiesIds)) {
                        $fail('Not correct order Activity id');
                        return;
                    }

                    $taskActivities = OrderActivities::where('id', $value)
                        ->first();
                    /** @var OrderActivities $taskActivities */

                    if($taskActivities) {
                        if (TimeService::getTimeDifferenceSub($this->user(), 'leave_bid', $taskActivities->date_end)) {
                            $fail('Time after date end of activities');
                        }
                    }
                },
            ]
        ];
    }
}
