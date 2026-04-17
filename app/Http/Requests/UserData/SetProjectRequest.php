<?php

namespace App\Http\Requests\UserData;

use App\Http\Requests\FormRequest;
use App\Enum\Role\RoleEnum;
use App\Models\Fields\Directory\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @property-read int userId
 * @property-read array projectId
 */
class SetProjectRequest extends FormRequest
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
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value == Auth::id()) {
                        $fail('Not use your id');
                    }
                }
            ],
            'projectId' => 'required|array',
            'projectId.*' => [
                'integer',
                function ($attribute, $value, $fail) {
                    $project = Project::where('id',$value)
                        ->where('date_end','>', Carbon::now())->first();
                    if (!$project) {
                        $fail('Project id not valid or date end of project is arrived');
                    }
                }
            ],
        ];
    }
}
