<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read int|null status
 */
class GetBidsRequest extends FormRequest
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
            'status'  => [
                'sometimes',
                'integer',
                Rule::in([
                    OrderStatusEnum::new->value,
                    OrderStatusEnum::notAccepted->value,
                    OrderStatusEnum::accepted->value,
                    OrderStatusEnum::canceled->value,
                    OrderStatusEnum::archive->value,
                ]),
            ]
        ];
    }
}
