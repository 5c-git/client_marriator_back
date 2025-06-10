<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Task;
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

                    $orderExists = Task::where(function ($query) use ($user,$value) {
                        $query->where('user_id', $user->id)->where('id', $value)
                            ->where('status', OrderStatusEnum::accepted);
                    })
                        ->orWhere(function ($query) use ($user,$value) {
                            $query->where('accept_user_id', $user->id)->where('id', $value)
                                ->where('status', OrderStatusEnum::accepted);
                        })
                        ->orWhere(function ($query) use ($user,$value) {
                            $userIdsSupervisor = $user->acceptedTasks?->pluck('id')->toArray();
                            $query->whereIn('id', $userIdsSupervisor)->where('id', $value)
                                ->where('status', OrderStatusEnum::accepted);
                        })->first();

                    if (!$orderExists) {
                        $fail('Not your task');
                    }
                },
            ],
            'taskActivityId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $taskActivitiesIds = Task::where('id',$this->taskId)->first()?->taskActivities?->pluck('id')->toArray();
                    if (!in_array($value,$taskActivitiesIds)) {
                        $fail('Not correct task Activity id');
                    }
                },
            ]
        ];
    }
}
