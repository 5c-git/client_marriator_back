<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
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
            'placeId' => [
                'required',
                'integer',
                'exists:directory_place,id',
                function ($attribute, $value, $fail) {
                    $places = Auth::user()->project
                        ->flatMap(fn($project) => $project->places)
                        ->unique('id')?->pluck('id')?->toArray();

                    if (!in_array($value,$places)) {
                        $fail('Not your place');
                        return;
                    }
                    $users = User::where('id',Auth::user()->id)
                        ->whereHas('project')
                        ->whereDoesntHave('project', function ($query) {
                            $query->where('date_end', '>', Carbon::now());
                        })
                        ->first();
                    if($users){
                        $fail('User project is out of date');
                    }
                },
            ],
            'selfEmployed' => [
                'sometimes',
                'boolean'
            ]
        ];
    }
}
