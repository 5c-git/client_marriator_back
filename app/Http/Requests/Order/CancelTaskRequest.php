<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Illuminate\Validation\Rule;
use App\Models\User;

/**
 * @property-read int|null status
 * @property-read int|null page
 * @property-read int|null perPage
 */
class CancelTaskRequest extends FormRequest
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
                    $taskExists = Task::query()
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->whereIn('status', [OrderStatusEnum::new->value,OrderStatusEnum::notAccepted->value])
                        ->first();

                    if (!$taskExists) {
                        $fail('Not your task');
                        return;
                    }

                    /** @var Task $taskExists */
                    /** @var TaskActivity $orderActivities */
                    $orderActivities = $taskExists->taskActivities()
                        ->orderBy('date_start')
                        ->first();

                    if($orderActivities) {
                        if (!TimeService::getTimeDifferenceSub(
                            $this->user(),
                            'cancel_task',
                            $orderActivities?->date_start
                        )) {
                            $fail('Task activities time start is arrived');
                        }
                    }
                },
            ],
        ];
    }
}
