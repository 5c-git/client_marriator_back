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
                        ->orWhere(function ($query) use ($user,$value,$userIdsSupervisor) {
                            $query->whereIn('accept_user_id', $userIdsSupervisor);
                        })
                        ->whereIn('status', [OrderStatusEnum::new,OrderStatusEnum::notAccepted])
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your task');
                    }
                },
            ],
            'radius' => 'required|integer',
            'price' => 'required|integer',
        ];
    }
}
