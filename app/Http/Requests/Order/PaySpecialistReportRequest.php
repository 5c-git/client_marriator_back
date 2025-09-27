<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\ReportStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Order;
use App\Models\Order\Report;
use App\Models\Order\Task;
use Illuminate\Validation\Rule;

class PaySpecialistReportRequest extends FormRequest
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
                    $report = Report::query()->where('id', $value)
                        ->where('user_id', $user->id)
                        ->where('status', ReportStatusEnum::forPay->value)
                        ->exists();

                    if (!$report) {
                        $fail('Report not found');
                    }
                },
            ]
        ];
    }
}
