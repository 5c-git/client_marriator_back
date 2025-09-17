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

class StartDayRequest extends FormRequest
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
                    if($report){
                        $fail('You have started day for this bid');
                    }

                    $orderExists = Bid::where(function ($query) use ($user,$value) {
                        $query->where(function ($query) use ($user, $value) {
                            $bidsIds = $user->acceptedBids()
                                ->wherePivot('accepted', BidAcceptingStatusEnum::work->value)
                                ->pluck('bid_id')
                                ->toArray();
                            $query->whereIn('id', $bidsIds)->where('id', $value)
                                ->where('status', OrderStatusEnum::accepted->value);
                        })->first();
                    });

                    if (!$orderExists) {
                        $fail('Not your bid');
                    }
                    /** @var Bid $orderExists */
                    if($orderExists->date_start && $orderExists->date_end && $orderExists->date_start->subHour() < Carbon::now() && $orderExists->date_end->addHours(12) > Carbon::now()){
                        $fail('Active date not start or this bid is ended');
                    }


                    $check = true;
                    if($orderExists->date_activity){
                        if(is_array($orderExists->date_activity)){
                            $dateActivity = $orderExists->date_activity;
                        }else{
                            $dateActivity = json_decode($orderExists->date_activity);
                        }
                        foreach ($dateActivity as $activity){
                            if(
                                $activity['timeStart'] &&
                                $activity['timeEnd'] &&
                                Carbon::parse($activity['timeStart'])->subHour() < Carbon::now() &&
                                Carbon::parse($activity['timeEnd'])->subHour() > Carbon::now()
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
                            $orderExists->date_start &&
                            $orderExists->date_end &&
                            $orderExists->date_start < Carbon::now() &&
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
        ];
    }
}
