<?php

namespace App\Http\Resources\Order;

use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\SearchRequest;
use App\Services\TimeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use App\Http\Resources\ViewActivityResource;
use App\Http\Resources\Order\DateActivityResource;

/**
 * @mixin \App\Models\Order\OrderActivities
 */
class OrderSActivitiesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'count' => $this->count,
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => DateActivityResource::collection(collect($this->date_activity)),
            'countSearch' => SearchRequest::query()->where('activity_id',$this->id)->count(),
            'buttonNeed' => $this->checkButtonNeed()
        ];
    }

    public function checkButtonNeed(): bool
    {
        $user = Auth::user();
        $check = true;
        $count = SearchRequest::query()->where('activity_id', $this->id)->count();
        if ($count < $this->count) {
            $check = false;
        }

        if($this->bidOrTask instanceof Order){
            $bid = Bid::query()->where('order_id',$this->bidOrTask->id)->where('activity_id', $this->id)->orderBy('id','desc')->first();
        }else{
            $bid = Bid::query()->where('task_id',$this->bidOrTask->id)->where('activity_id', $this->id)->orderBy('id','desc')->first();
        }

        if (!$bid) {
            $check = false;
        }

        if ($bid && $user) {
            /** @var Bid $bid */
            if(!TimeService::getTimeDifferenceAdd($user,'repeat_bid',$bid->created_at)){
                $check = false;
            }
        }

        return $check;
    }
}
