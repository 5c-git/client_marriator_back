<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use Illuminate\Validation\Rule;

class BidDataRequest extends FormRequest
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
            'bidId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $taskExists = Bid::query()
                        ->whereIn('user_id',$userIdsSupervisor)
                        ->where('id',$value)
                        ->orWhere(function ($query) use ($user,$value,$userIdsSupervisor) {
                            $query->whereIn('accept_user_id', $userIdsSupervisor);
                        })
                        ->whereIn('status', [OrderStatusEnum::new->value])
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your bid');
                    }
                },
            ],
            'radius' => 'sometimes|integer',
            'price' => 'sometimes|integer',
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
        ];
    }
}
