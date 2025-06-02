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
class TaskShortResource extends JsonResource
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
