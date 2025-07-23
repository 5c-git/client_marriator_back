<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use \App\Http\Resources\Order\OrderActivitiesResource;


/**
 * @mixin \App\Models\Order\Task
 */
class TaskResource extends JsonResource
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
            'status' => $this->status->getStatusName(),
            'place' => new PlaceResource($this->place),
            'user' => new ShortUserResource($this->user),
            'acceptUser' => new ShortUserResource($this->acceptUser),
            //'price' => $this->price,
            //'priceResult' => $this->price*($this->self_employed?0.94:0.87),
            //'income' => $this->income,
            //'scopeOfServices' => $this->scope_of_services,
            'orderActivities' => OrderActivitiesResource::collection($this->taskActivities),
            'acceptedTasks' => ShortUserResource::collection($this->acceptingUsers),
        ];
    }
}
