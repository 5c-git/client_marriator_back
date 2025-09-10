<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;
use App\Models\User;

/**
 * @property-read int|null status
 * @property-read int|null page
 * @property-read int|null perPage
 * @property-read int taskId
 */
class InstructTaskRequest extends FormRequest
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
                        ->whereIn('user_id',$userIdsSupervisor)
                        ->whereIn('status', [OrderStatusEnum::new->value,OrderStatusEnum::notAccepted->value])
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your task');
                    }
                },
            ],
            'supervisorId' => [
                'sometimes',
                'integer',
                function ($attribute, $value, $fail) {

                    $ids = $value;


                    $validIds = User::where('id', $ids)
                        ->whereHas('roles', fn($q) => $q->where('role_id', RoleEnum::supervisor->value))
                        ->pluck('id')
                        ->toArray();
                    if(!$validIds) {
                        $fail('Supervisor ids not exist ' . print_r($validIds,true));
                    }
                }
            ],
        ];
    }
}
