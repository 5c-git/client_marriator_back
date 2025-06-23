<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
use App\Models\Order\Request;
/**
 * @property-read int requestId
 */
class CancelRequestRequest extends FormRequest
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
            'requestId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();

                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    $orderExists = Request::where(function ($query) use ($user,$value,$userIdsSupervisor) {
                        $query->whereIn('user_id', $userIdsSupervisor)->where('id', $value)
                            ->where('status', OrderStatusEnum::notAccepted);
                    })->first();

                    if (!$orderExists) {
                        $fail('Not your order');
                    }
                },
            ],
        ];
    }
}
