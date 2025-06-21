<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;

class DeleteTaskActivityRequest extends FormRequest
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
            'taskActivityId' => 'required|integer|exists:task_activities,id',
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your task');
                    }
                },
            ],
        ];
    }
}
