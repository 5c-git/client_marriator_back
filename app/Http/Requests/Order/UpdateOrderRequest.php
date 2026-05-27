<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\User;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
                'sometimes',
                'integer',
                'exists:directory_project,id',
                function ($attribute, $value, $fail) {
                    $order = Order::query()->where('id',$this->orderId)->first();
                    $project = collect();
                    if($order) {
                        $project = Auth::user()?->project()
                            ->whereHas('places', function ($query) use ($order) {
                                $query->where('directory_place.id', $order->place_id);
                            })
                            ->where('date_end', '>=', Carbon::now())
                            ->where('self_employed', $order->self_employed)
                            ->pluck('id')?->toArray();
                    }

                    if (!in_array($value,$project)) {
                        $fail('Not your project');
                    }
                },
            ],
            'selfEmployed' => 'sometimes|boolean',
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
                        ->whereHas('project')
                        ->whereDoesntHave('project', function ($query) {
                            $query->where('date_end', '>', Carbon::now());
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
        ];
    }
}
