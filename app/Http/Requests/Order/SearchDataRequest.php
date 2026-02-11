<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Fields\Directory\Project;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\SearchRequest;
use App\Models\Order\Task;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SearchDataRequest extends FormRequest
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
            'searchId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $taskExists = SearchRequest::query()
                        ->whereIn('user_id',$userIdsSupervisor)
                        ->where('id',$value)
                        ->orWhere(function ($query) use ($user,$value,$userIdsSupervisor) {
                            $query->whereIn('accept_user_id', $userIdsSupervisor);
                        })
                        ->whereIn('status',0)
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your search');
                        return;
                    }
                },
            ],
            'radius' => 'sometimes|integer',
            'price' => 'sometimes|integer',
            'viewActivityId' => 'sometimes|exists:directory_view_activities,id',
            'count' => 'sometimes|integer|min:1',
            'dateStart' => [
                'sometimes',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    /** @var  $bid Bid */
                    $bid = SearchRequest::query()->where('id', $this->searchId)->first();
                    /** @var  $project Project */
                    $project = $bid->order?->user?->project?->first()
                        ?? $bid->task?->project
                        ?? $bid->task?->order?->user?->project?->first();

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
                'sometimes',
                'date',
                'after:dateStart',
                function ($attribute, $value, $fail) {
                    /** @var  $bid Bid */
                    $bid = Bid::query()->where('id', $this->searchId)->first();
                    /** @var  $project Project */
                    $project = $bid->order?->user?->project?->first()
                        ?? $bid->task?->project
                        ?? $bid->task?->order?->user?->project?->first();

                    if ($project && $project->date_end) {
                        $dateEnd = Carbon::parse($value);
                        $projectDateEnd = Carbon::parse($project->date_end);

                        if ($dateEnd->gt($projectDateEnd)) {
                            $fail('The activity end date must be before or equal to the project end date.');
                        }
                    }
                }
            ],
            'needFoto' => 'sometimes|boolean',

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
