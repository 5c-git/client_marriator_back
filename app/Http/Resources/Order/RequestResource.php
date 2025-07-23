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
 * @mixin \App\Models\Order\Request
 */
class RequestResource extends JsonResource
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
            'status' => $this->status->getStatusName(),
            'selfEmployed' => (bool)$this->self_employed,
            'radius' => $this->radius,
            'price' => $this->price,
            'priceResult' => $this->price*($this->self_employed?0.94:0.87),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'count' => $this->count,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'need_foto' => (bool)$this->need_foto,
            'date_activity' => $this->date_activity,
        ];
    }
}
