<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\ViewActivityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;

/**
 * @mixin \App\Models\Order\Bid
 */
class BidShortResource extends JsonResource
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
            'place' => new PlaceResource($this->place),
            'selfEmployed' => (bool)$this->self_employed,
            'status'=>$this->status->getStatusName(),
        ];
    }
}
