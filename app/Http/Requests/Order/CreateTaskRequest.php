<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
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
                    }
                },
            ],
            'projectId' => [
                'required',
                'integer',
                'exists:directory_project,id',
                function ($attribute, $value, $fail) {
                    $project = Auth::user()->project
                        ->unique('id')?->pluck('id')?->toArray();

                    if (!in_array($value,$project)) {
                        $fail('Not your project');
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
