<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use Illuminate\Validation\Rule;

/**
 * @property-read int bidId
 * @property-read int specialistId

 */
class GetJobRequest extends FormRequest
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
            'bidId'=> [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();

                    $orderExists = Bid::query()->where(function ($query) use ($user,$value) {
                        $userIdsSupervisor = $user->acceptedBids?->pluck('id')->toArray();
                        $query->whereIn('id', $userIdsSupervisor)->where('id', $value);
                    })->orWhere(function ($query) use ($user,$value) {
                        $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                        $userIdsSupervisor[] = $user->id;
                        $query->whereIn('user_id', $userIdsSupervisor)->where('id', $value);
                    })->first();

                    if (!$orderExists) {
                        $fail('Not your job');
                    }
                },
            ],
            'specialistId'=>
                [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        $user = auth()->user();
                        $roles = $user?->roles?->pluck('id')->toArray();


                        if($user->id == $value && !in_array(RoleEnum::specialist->value,$roles))
                        {
                            $fail('Not your job');
                        }

                        if($user->id != $value &&
                            (
                                !in_array(RoleEnum::supervisor->value,$roles) &&
                                !in_array(RoleEnum::manager->value,$roles)
                            )
                        )
                        {
                            $fail('Not your job');
                        }


                        $bid = Bid::with([
                            'acceptingUsers' => function ($query) use ($value) {
                                $query->where('user_id', $value);
                            }
                        ])
                            ->where('id', $this->bidId)
                            ->whereHas('acceptingUsers', function ($query) use ($value) {
                                $query->where('user_id', $value);
                            })
                            ->first();

                        if(!$bid){
                            $fail('Not your job');
                        }


                    },
                ]
        ];
    }
}
