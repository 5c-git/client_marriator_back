<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read int taskId
 * @property-read int taskActivityId
 */
class CreateBidFromTaskRequest extends FormRequest
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
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();

                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Task::where(function ($query) use ($user,$value,$userIdsSupervisor) {
                        $query->whereIn('user_id', $userIdsSupervisor)->where('id', $value)
                            ->whereIn('status', [
                                OrderStatusEnum::accepted->value,
                                ]
                            );
                    })
                        ->orWhere(function ($query) use ($user,$value,$userIdsSupervisor) {
                            $query->whereIn('accept_user_id', $userIdsSupervisor)->where('id', $value)
                                ->whereIn('status', [
                                    OrderStatusEnum::accepted->value,
                                ]);
                        })
                        ->first();

                    if (!$orderExists) {
                        $fail('Not your task');
                        return;
                    }
                    /** @var Task $orderExists */
                    $bids = $orderExists->bid?->where('activity_id', $this->orderActivityId)->first();
                    if ($bids) {
                        /** @var Bid $bids */
                        if(TimeService::getTimeDifferenceAdd($this->user(),'repeat_bid',$bids->created_at)){
                            $fail('Time before date of create new bid');
                        }
                    }
                },
            ],
            'taskActivityId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $taskActivitiesIds = Task::where('id',$this->taskId)->first()?->taskActivities?->pluck('id')->toArray();
                    if (!$taskActivitiesIds || !in_array($value,$taskActivitiesIds)) {
                        $fail('Not correct task Activity id');
                        return;
                    }

                    $taskActivities = TaskActivity::where('id', $value)->first();
                    /** @var TaskActivity $taskActivities */

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
