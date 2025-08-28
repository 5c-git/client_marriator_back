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
            'user' => new ShortUserResource($this->user),
            'acceptUserId' => new ShortUserResource($this->acceptUser),
            'status' => $this->status->value,
            'selfEmployed' => (bool)$this->self_employed,
            'place' => new PlaceResource($this->place),
            'radius' => $this->radius,
            'price' => $this->price,
            'priceResult' => $this->price*($this->self_employed?0.94:0.87),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => $this->date_activity,
        ];
    }
}
