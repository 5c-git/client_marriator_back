<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Services\TimeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'placeId' => [
                'sometimes',
                'integer',
                'exists:directory_place,id',
                function ($attribute, $value, $fail) {
                    $places = Auth::user()->project
                        ->flatMap(fn($project) => $project->places)
                        ->unique('id')?->pluck('id')?->toArray();

                    if (!in_array($value,$places)) {
                        $fail('Not your place');
                    }
                },
            ],
            'projectId' => [
                Rule::requiredIf($this->has('placeId')),
                'integer',
                'exists:directory_project,id',
                function ($attribute, $value, $fail) {
                    $project = Auth::user()->project
                        ->unique('id')?->pluck('id')?->toArray();

                    if (!in_array($value,$project)) {
                        $fail('Not your project');
                    }
                },
            ],
            'selfEmployed' => 'sometimes|boolean',
            //'price' => 'sometimes|float|min:1',
            //'income' => 'sometimes|float|min:1',
            //'scope_of_services' => 'sometimes|integer|min:1',
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Task::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSupervisor)
                        ->whereNull('order_id')
                        ->where('status',OrderStatusEnum::new->value)
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

                    if(!TimeService::getTimeDifferenceSub($this->user(),'change_task',$orderActivities?->date_start)){
                        $fail('Task activities time start is arrived after change task');
                    }
                },
            ],
        ];
    }
}
