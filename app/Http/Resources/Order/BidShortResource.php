<?php

namespace App\Http\Resources\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\Order\StatisticResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ViewActivityResource;
use App\Models\Fields\Directory\Radius;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;

/**
 * @mixin \App\Models\Order\Bid
 */
class BidShortResource extends JsonResource
{
    private int $radiusDefault = 5;
    private int $radius = 0;
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
            'user' => new ShortUserResource($this->user),
            'status' => $this->getActualStatus(),
            'selfEmployed' => (bool)$this->self_employed,
            'place' => new PlaceResource($this->place),
            'radius' => $this->radius ?? $this->getRadius(),
            'price' => (float)$this->price,
            'priceResult' => (float)$this->price*($this->self_employed?0.94:0.87),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => DateActivityResource::collection(collect($this->date_activity)),
            'order' => new ShortOrderResource($this->order),
            'task' => new TaskShortResource($this->task),
            'count' => $this->count,
            'statistic' => $this->getStatistic(),
            'project'=> new ProjectResource($this->getProject()),
        ];
    }

    private function getActualStatus(): int
    {
        if(!$this->acceptingUsers->count()) {
            return $this->status->value;
        }else{
            $statusW = 0;
            $statusA = 0;
            $statusC = 0;
            foreach ($this->acceptingUsers as $users){
                if(!empty($users->pivot) && !empty($users->pivot->accepted)){
                    if($users->pivot->accepted === BidAcceptingStatusEnum::work->value){
                        $statusW++;
                        break;
                    }
                    if($users->pivot->accepted === BidAcceptingStatusEnum::notAccepted->value){
                        $statusA++;
                        break;
                    }
                    if($users->pivot->accepted === BidAcceptingStatusEnum::consideration->value){
                        $statusC++;
                        break;
                    }
                }
            }
            if($statusW){
                return 8;
            }
            if($statusC){
                return 7;
            }
            if($statusA){
                return 7;
            }
        }
        return $this->status->value;
    }

    private function getProject(){
        return $this->order?->user?->project?->first()
            ?? $this->task?->project
            ?? $this->task?->order?->user?->project?->first();
    }

    private function getRadius()
    {
        if(!$this->radius){
            $radius = Radius::where('default',true)->first();
            if(!$radius) {
                $this->radius = $this->radiusDefault;
            }else{
                $this->radius = $radius->value;
            }
        }
        return $this->radius;
    }

    private function getStatistic(){
        $statusCounts = DB::table('accept_bid')
            ->where('bid_id', $this->id)
            ->groupBy('accepted')
            ->select('accepted', DB::raw('COUNT(*) as count'))
            ->get();
        return StatisticResource::collection($statusCounts);
    }
}
