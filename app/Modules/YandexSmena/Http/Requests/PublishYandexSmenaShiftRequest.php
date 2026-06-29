<?php

namespace Modules\YandexSmena\Http\Requests;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;

class PublishYandexSmenaShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderId' => ['sometimes', 'integer'],
            'orderActivityId' => [
                'sometimes',
                'required_with:orderId',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! $this->has('orderId')) {
                        return;
                    }

                    $order = Order::where('id', $this->input('orderId'))->first();

                    if ($order === null) {
                        $fail('Order not found');

                        return;
                    }

                    if (! $this->userCanAccessOrder($order)) {
                        $fail('Not your order');

                        return;
                    }

                    $activity = OrderActivities::where('id', $value)
                        ->where('order_id', $order->id)
                        ->first();

                    if ($activity === null) {
                        $fail('Order activity does not belong to the order');
                    }
                },
            ],
            'taskId' => ['sometimes', 'integer'],
            'taskActivityId' => [
                'sometimes',
                'required_with:taskId',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! $this->has('taskId')) {
                        return;
                    }

                    $task = Task::where('id', $this->input('taskId'))->first();

                    if ($task === null) {
                        $fail('Task not found');

                        return;
                    }

                    if (! $this->userCanAccessTask($task)) {
                        $fail('Not your task');

                        return;
                    }

                    $activity = TaskActivity::where('id', $value)
                        ->where('task_id', $task->id)
                        ->first();

                    if ($activity === null) {
                        $fail('Task activity does not belong to the task');
                    }
                },
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasOrder = $this->filled('orderId') || $this->filled('orderActivityId');
            $hasTask = $this->filled('taskId') || $this->filled('taskActivityId');

            if (! $hasOrder && ! $hasTask) {
                $validator->errors()->add('orderId', 'Either orderId + orderActivityId or taskId + taskActivityId is required');

                return;
            }

            if ($hasOrder && $hasTask) {
                $validator->errors()->add('orderId', 'Provide either order pair or task pair, not both');
            }
        });
    }

    private function userCanAccessOrder(Order $order): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $allowedUserIds = $user->supervisors->pluck('id')->toArray();
        $allowedUserIds[] = $user->id;

        return in_array($order->user_id, $allowedUserIds, true)
            || in_array($order->accept_user_id, $allowedUserIds, true);
    }

    private function userCanAccessTask(Task $task): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $allowedUserIds = $user->supervisors->pluck('id')->toArray();
        $allowedUserIds[] = $user->id;

        return in_array($task->user_id, $allowedUserIds, true)
            || in_array($task->accept_user_id, $allowedUserIds, true);
    }
}
