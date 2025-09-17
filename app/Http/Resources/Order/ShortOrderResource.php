<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\StatisticResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;

/**
 * @mixin \App\Models\Order\Order
 */
class ShortOrderResource extends JsonResource
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
            'user' => new ShortUserResource($this->user),
            'statistic' => $this->getStatistic()
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
