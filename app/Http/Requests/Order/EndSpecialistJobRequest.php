<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read int bidId
 * @property-read int specialistId
 */
class EndSpecialistJobRequest extends FormRequest
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
                    $orderExists = Bid::where(function ($query) use ($user,$value,$userIdsSupervisor) {
                        $query->whereIn('user_id', $userIdsSupervisor)->where('id', $value);
                    })->first();

                    if (!$orderExists) {
                        $fail('Not your bid or status is not notAccepted');
                    }
                },
            ],
            'specialistId'=>
                [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        $user = User::where('id',$value)->first();
                        $check = false;
                        if ($user->acceptedBids) {
                            foreach ($user->acceptedBids as $acceptedBid) {
                                if ($acceptedBid->id == $this->bidId) {
                                    $check = true;
                                }
                            }
                        }
                        if(!$check){
                            $fail('Specialist not accepted this bid');
                            return;
                        }
                        if($user) {
                            $orderExists = Bid::where(function ($query) use ($user) {
                                $userIdsSupervisor = $user->acceptedBids?->pluck('id')->toArray();
                                $query->whereIn('id', $userIdsSupervisor)->where('id', $this->bidId);
                            })->first();

                            if (!$orderExists) {
                                $fail('Specialist not accepted for bid');
                            }
                        }else{
                            $fail('Specialist not found');
                        }
                    },
                ]
        ];
    }
}
