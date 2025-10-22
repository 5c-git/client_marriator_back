<?php

namespace App\Http\Requests\Document;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read string code
 */
class SignedDocumentsSendCodeRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
            ],
        ];
    }
}
