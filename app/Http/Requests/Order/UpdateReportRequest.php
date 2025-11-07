<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\ReportStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Report;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
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
            'reportId' => [
                'required',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSpecialist = $user->managerSpecialist->pluck('id')->toArray();
                    $userIdsSpecialist = array_merge($userIdsSpecialist, $user->supervisorSpecialist->pluck('id')->toArray());
                    $userIdsSpecialist = array_unique($userIdsSpecialist);
                    $report = Report::query()->where('id', $value)
                        ->whereIn('user_id', $userIdsSpecialist)
                        ->whereIn('status', [ReportStatusEnum::reported->value,
                                             ReportStatusEnum::end->value,
                                             ReportStatusEnum::accept->value,
                                             ReportStatusEnum::notEnded->value,])
                        ->exists();

                    if (!$report) {
                        $fail('Report not found or status after accepted');
                    }
                },
            ],
            'hours' => 'required|numeric',
            'reasons' => 'sometimes|array|min:1',
            'reasons.*.reasonId' => 'required|integer|exists:directory_reasons,id',
            'reasons.*.amount' => 'required|integer|min:1'
        ];
    }
}
