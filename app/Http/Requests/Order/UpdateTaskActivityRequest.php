<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTaskActivityRequest extends FormRequest
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
            'taskActivity' => 'required|exists:task_activities,id',
            'viewActivityId' => 'sometimes|exists:directory_view_activities,id',
            'count' => 'sometimes|integer|min:1',
            'dateStart' => 'sometimes|date|after:now',
            'dateEnd' => 'sometimes|date|after:dateStart',
            'needFoto' => 'sometimes|boolean',

            'dateActivity' => 'sometimes|array|min:1',
            'dateActivity.*.timeStart' => 'required|date|after:now',
            'dateActivity.*.timeEnd' => 'required|date|after:timeStart',
            'dateActivity.*.placeIds' => 'sometimes|array|min:1',
            'dateActivity.*.placeIds.*' => [
                'required',
                Rule::exists('directory_place', 'id'),
            ],
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->managerSpecialist->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->supe;
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->exists();

                    if (!$orderExists) {
                        $fail('Not your order');
                    }
                },
            ],
        ];
    }
}
