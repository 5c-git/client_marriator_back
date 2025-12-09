<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Fields\Directory\Project;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Carbon\Carbon;
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
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->whereIn('status', [
                                OrderStatusEnum::new->value,
                                OrderStatusEnum::notAccepted->value,
                            ]
                        )
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
            'viewActivityId' => 'required|exists:directory_view_activities,id',
            'count' => 'required|integer|min:1',
            'dateStart' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $task = Task::query()->where('id', $this->taskId)->first();
                    /** @var  $project Project */
                    $project = $task->project ? $task->project : $task->order->user->project->first();
                    if ($project && $project->date_start) {
                        $dateStart = Carbon::parse($value);
                        $projectDateStart = Carbon::parse($project->date_start);

                        if ($dateStart->lt($projectDateStart)) {
                            $fail('The activity start date must be after or equal to the project start date.');
                        }
                    }
                }
            ],
            'dateEnd' => [
                'required',
                'date',
                'after:dateStart',
                function ($attribute, $value, $fail) {
                    $task = Task::query()->where('id', $this->taskId)->first();
                    /** @var  $project Project */
                    $project = $task->project ? $task->project : $task->order->user->project->first();
                    if ($project && $project->date_end) {
                        $dateEnd = Carbon::parse($value);
                        $projectDateEnd = Carbon::parse($project->date_end);

                        if ($dateEnd->gt($projectDateEnd)) {
                            $fail('The activity end date must be before or equal to the project end date.');
                        }
                    }
                }
            ],
            'needFoto' => 'required|boolean',

            'dateActivity' => 'sometimes|array|min:1',
            'dateActivity.*.timeStart' => 'required|date|after:now',
            'dateActivity.*.timeEnd' => 'required|date|after:timeStart|before_or_equal:dateEnd',
            'dateActivity.*.placeIds' => 'sometimes|array|min:1',
            'dateActivity.*.placeIds.*' => [
                'required',
                Rule::exists('directory_place', 'id'),
            ],
        ];
    }
}
