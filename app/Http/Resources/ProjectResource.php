<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Fields\Directory\Project
 */
class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'timeStart' => $this->time_start?->format('H:i'),
            'timeEnd' => $this->time_end?->format('H:i'),
            'brand' => BrandResource::collection($this->brands)
        ];
    }
}
