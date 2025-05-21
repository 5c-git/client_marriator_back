<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Fields\Directory\Place;

class DateActivityResource extends JsonResource
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
            'timeStart' => $this['timeStart'],
            'timeEnd' => $this['timeEnd'],
            'places' => $this->getPlaces($this['placeIds']),
        ];
    }

    private function getPlaces(array $placeIds)
    {
       $places = Place::query()->whereIn('id',$placeIds)->get();
       return PlaceResource::collection($places);
    }
}
