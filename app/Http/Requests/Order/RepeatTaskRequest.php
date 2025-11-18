<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RepeatTaskRequest extends FormRequest
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
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->where('status',OrderStatusEnum::canceled->value)
                        ->whereNull('order_id')
                        ->first();

                    if (!$orderExists) {
                        $fail('Not your task');
                        return;
                    }

                    /** @var Task $orderExists */
                    /** @var TaskActivity $orderActivities */
                    $orderActivities = $orderExists->taskActivities()
                        ->orderBy('date_start')
                        ->first();

                    if($orderActivities) {
                        if (!TimeService::getTimeDifferenceSub(
                            $this->user(),
                            'change_task',
                            $orderActivities?->date_start
                        )) {
                            $fail('Task activities time start is arrived after change task');
                        }
                    }
                },
            ],
        ];
    }
}
