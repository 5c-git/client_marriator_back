<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderActivitiesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            'place' => new PlaceResource($this->place),
            'user' => new ShortUserResource($this->user),
            'orderActivities' => OrderActivitiesResource::collection($this->orderActivities)
        ];
    }
}
