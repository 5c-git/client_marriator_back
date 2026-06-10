<?php

namespace App\Http\Requests\UserData;

use App\Http\Requests\FormRequest;
use App\Models\User;
use App\Models\User\UserContractData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @property-read array counterpartyIds
 */
class SetCounterpartyForOrderRequest extends FormRequest
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
            'counterpartyIds' => 'required|array',
            'counterpartyIds.*' => [
                'integer',
                Rule::exists('directory_counterparty', 'id'),
                function ($attribute, $value, $fail) {
                    /** @var  $user User */
                    $user = Auth::user();
                    $userContractData = UserContractData::query()
                        ->where('user_id', $user->id)
                        ->where('counterparty_id',$value)
                        ->where('date_start', '<=', Carbon::now())
                        ->where('date_end', '>=', Carbon::now())
                        ->first();
                    if (!$userContractData) {
                        $fail('Not your counterparty id');
                    }

                }
            ],
        ];
    }
}
