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
            'specialistId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {

                    $validIds = User::where('id', $value)
                        ->whereHas('roles', fn($q) => $q->where('role_id', RoleEnum::specialist))
                        ->pluck('id')
                        ->toArray();

                    if(!$validIds) {
                        $fail('Supervisor ids not exist ' . $value);
                    }
                }
            ],
        ];
    }
}
