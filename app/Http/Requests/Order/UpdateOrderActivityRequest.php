<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Fields\Directory\Project;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\User;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderActivityRequest extends FormRequest
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
            'orderId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $orderExists = Order::query()->where('id', $value)
                        ->where('user_id', auth()->id())
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

                    $users = User::where('id',Auth::user()->id)
                        ->whereHas('project', function ($query) {
                            $query->where('date_end', '<', Carbon::now());
                        })
                        ->first();
                    if($users){
                        $fail('User project is out of date');
                    }

//                    /** @var Order $orderExists */
//                    /** @var OrderActivities $orderActivities */
//                    $orderActivities = $orderExists->orderActivities()
//                        ->orderBy('date_start')
//                        ->first();
//
//                    if($orderActivities) {
//                        if (!TimeService::getTimeDifferenceSub(
//                            $this->user(),
//                            'change_order',
//                            $orderActivities?->date_start
//                        )) {
//                            $fail('Order activities time start is arrived after change order');
//                        }
//                    }
                },
            ],
            'viewActivityId' => 'sometimes|exists:directory_view_activities,id',
            'orderActivity' => 'required|exists:order_activities,id',
            'count' => 'sometimes|integer|min:1',
            'dateStart' => [
                'sometimes',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $order = Order::query()->where('id', $this->orderId)->first();
                    /** @var  $project Project */
                    $project = $order->user->project->first();
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
                    $order = Order::query()->where('id', $this->orderId)->first();
                    /** @var  $project Project */
                    $project = $order->user->project->first();
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
