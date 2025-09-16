<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
use App\Models\User;
/**
 * @property-read int orderId
 */
class ConvertTaskRequest extends FormRequest
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
                    $orderExists = false;
                    $user = auth()->user();
                    $orders = $user->acceptOrders?->pluck('id')?->toArray();
                    if(!empty($orders) && in_array($value,$orders)) {
                        $orderExists = Order::query()
                            ->where('id', $value)
                            ->where('status',OrderStatusEnum::accepted->value)
                            ->exists();
                    }
                    if (!$orderExists) {
                        $fail('Order not accepted');
                    }
                },
            ],
            'supervisorId' => [
                'sometimes',
                'int',
                'exists:users,id',
                function ($attribute, $value, $fail) {

                        $validIds = User::where('id', $value)
                            ->whereHas('roles', function ($query) {
                                $query->where('role_id', RoleEnum::supervisor->value);
                            })
                            ->pluck('id')
                            ->toArray();

                        $invalidIds = array_diff($value, $validIds);

                        if (!empty($invalidIds)) {
                            $fail('Supervisor ids not exist ' . implode(', ', $invalidIds));
                        }
                }
            ],
        ];
    }
}
