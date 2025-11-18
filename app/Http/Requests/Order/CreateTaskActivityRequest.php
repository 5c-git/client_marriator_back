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

class CreateTaskActivityRequest extends FormRequest
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
            'viewActivityId' => 'required|exists:directory_view_activities,id',
            'count' => 'required|integer|min:1',
            'dateStart' => 'required|date|after:now',
            'dateEnd' => 'required|date|after:dateStart',
            'needFoto' => 'required|boolean',

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
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->where('status',OrderStatusEnum::new->value)
                        ->first();

                    if (!$orderExists) {
                        $fail('Not your order');
                        return;
                    }

//                    /** @var Task $orderExists */
//                    /** @var TaskActivity $orderActivities */
//                    $orderActivities = $orderExists->taskActivities()
//                        ->orderBy('date_start')
//                        ->first();
//
//                    if($orderActivities) {
//                        if (!TimeService::getTimeDifferenceSub(
//                            $this->user(),
//                            'change_task',
//                            $orderActivities?->date_start
//                        )) {
//                            $fail('Task activities time start is arrived after change task');
//                        }
//                    }
                },
            ],
        ];
    }
}
