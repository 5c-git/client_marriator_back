<?php

namespace App\Http\Requests\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Report;
use App\Models\Order\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

class EndJobRequest extends FormRequest
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
            'bidId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();

                    $orderExists = Bid::where(function ($query) use ($user,$value) {
                        $query->where(function ($query) use ($user, $value) {
                            $bidsIds = $user->acceptedBids()
                                ->wherePivot('accepted', BidAcceptingStatusEnum::work->value)
                                ->pluck('bid_id')
                                ->toArray();
                            $query->whereIn('id', $bidsIds)->where('id', $value);
                        })->first();
                    });

                    if(!$orderExists){
                        $fail('Job not found');
                    }
                },
            ]
        ];
    }
}
