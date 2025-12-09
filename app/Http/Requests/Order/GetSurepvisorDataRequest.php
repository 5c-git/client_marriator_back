<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;

/**
 * @property-read int bidId
 */
class GetSurepvisorDataRequest extends FormRequest
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
            'taskId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $taskExists = Task::query()
                        ->where('user_id',$user->id)
                        ->exists();

                    if (!$taskExists) {
                        $fail('Not your task');
                    }
                },
            ],
        ];
    }
}
