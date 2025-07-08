<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use App\Http\Resources\ViewActivityResource;
use App\Http\Resources\Order\DateActivityResource;

/**
 * @mixin \App\Models\Order\OrderActivities
 */
class OrderActivitiesResource extends JsonResource
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
            'dateActivity' => DateActivityResource::collection(collect($this->date_activity))
        ];
    }
}
