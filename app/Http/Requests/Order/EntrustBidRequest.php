<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;
use App\Models\User;

/**
 * @property-read int bidId
 */
class EntrustBidRequest extends FormRequest
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
                        ->where('status', OrderStatusEnum::new)
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your bid');
                    }
                },
            ],
            'specialistIds' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $taskExists = Bid::query()
                        ->where('id',$this->bidId)->first();
                    if($taskExists){
                        if($taskExists->count < count($value)){
                            $fail('Supervisor count more than need count');
                        }
                        if($taskExists->count > count($value)){
                            $fail('Supervisor count less than necessary need count');
                        }
                    }

                    $validIds = User::whereIn('id', $value)
                        ->whereHas('roles', fn($q) => $q->where('role_id', RoleEnum::specialist->value))
                        ?->pluck('id')
                        ->toArray();

                    if(!$validIds || count($validIds) < count($value)) {
                        $fail('Supervisor ids not exist ' . implode(' ,',array_diff($value, $validIds)));
                    }
                }
            ],
        ];
    }
}
