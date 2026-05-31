<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\Order\StatisticResource;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;


/**
 * @mixin \App\Models\Order\Order
 */
class OneOrderResource extends JsonResource
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
            'selfEmployed' => (bool)$this->self_employed,
            'status' => $this->status->value,
            'place' => new PlaceResource($this->place),
            'user' => new ShortUserResource($this->user),
            'orderActivities' => OrderSActivitiesResource::collection($this->orderActivities),
            'acceptUser' => new ShortUserResource($this->acceptUser),
            'statistic' => $this->getStatistic(),
            'project' => new ProjectResource($this->project)
        ];
    }

    private function getStatistic(){
        $statusCounts = DB::table('accept_bid')
            ->where('order_id', $this->id)
            ->groupBy('accepted')
            ->select('accepted', DB::raw('COUNT(*) as count'))
            ->get();
        return StatisticResource::collection($statusCounts);
    }
}
