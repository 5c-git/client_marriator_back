<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use Illuminate\Validation\Rule;

/**
 * @property-read int bidId
 * @property-read int specialistId

 */
class PayReportRequest extends FormRequest
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
            'reportId'=> [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $userIdsSupervisor = $user->supervisors->pluck('id')->toArray();
                    $userIdsSupervisor[] = $user->id;
                    /** @var  $report Report */
                    $report = Report::where('id',$value)->whereIn('status',[
                        ReportStatusEnum::accept->value,
                    ])->first();
                    if(!$report){
                        $fail('Report not found');
                    }
                    $bid = $report->bid;
                    if (!in_array($bid->user_id, $userIdsSupervisor)) {
                        $fail('Not your specialist report');
                    }
                },
            ],
        ];
    }
}
