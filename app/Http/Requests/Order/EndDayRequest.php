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

class EndDayRequest extends FormRequest
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
                    $report = Report::query()
                        ->where('user_id',$user->id)
                        ->where('bid_id',$value)
                        ->where('status',ReportStatusEnum::start->value)
                        ->first();
                    if(!$report){
                        $fail('You dont have started day for this bid');
                    }

                    $orderExists = Bid::where(function ($query) use ($user,$value) {
                        $query->where(function ($query) use ($user, $value) {
                            $bidsIds = $user->acceptedBids()
                                ->pluck('bid_id')
                                ->toArray();
                            $query->whereIn('id', $bidsIds)->where('id', $value)
                                ->where('status', OrderStatusEnum::accepted->value);
                        });
                    })->first();

                    if (!$orderExists) {
                        $fail('Not your bid');
                        return;
                    }
                    $this->bidObj = $orderExists;
                    /** @var Bid $orderExists */
                    if($orderExists->date_start && $orderExists->date_end && $orderExists->date_start->subHour() < Carbon::now() && $orderExists->date_end->addHours(12) >Carbon::now()){
                        $fail('Active date not start or this bid is ended');
                    }


                    $check = true;
                    if($orderExists->date_activity){
                        if(is_array($orderExists->date_activity)){
                            $dateActivity = $orderExists->date_activity;
                        }else{
                            $dateActivity = json_decode($orderExists->date_activity,true);
                        }
                        foreach ($dateActivity as $activity){
                            if(
                                $activity['timeStart'] &&
                                $activity['timeEnd'] &&
                                Carbon::parse($activity['timeEnd'])->addHours(12) > Carbon::now()
                            )
                            {
                                $check = true;
                                break;
                            }else{
                                $check = false;
                            }
                        }
                    }else{
                        if(
                            $orderExists->date_end &&
                            $orderExists->date_end > Carbon::now()
                        ){
                            $check = true;
                        }else{
                            $check = false;
                        }
                    }

                    if($check === false)
                    {
                        $fail('Date start incorrect');
                    }

                },
            ],
            "reports" => [
                $this->bidObj && $this->bidObj->need_foto ? 'required' : 'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->bidObj && $this->bidObj->need_foto) {
                        if (count($value) === 0) {
                            $fail('The report field is required when bid requires photos.');
                        }
                        foreach ($value as $file) {
                            if (!($file instanceof UploadedFile)) {
                                $fail('Each item in report must be a file.');
                            }
                        }
                    }
                },
            ]
        ];
    }
}
