<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RoleResource;
use Carbon\Carbon;
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
            'id' => $this['id']?? null,
            'timeStart' => $this['timeStart']?Carbon::parse($this['timeStart'])->timezone('Europe/Moscow')->format('Y-m-d\TH:i:sP'):null,
            'timeEnd' => $this['timeEnd']?Carbon::parse($this['timeEnd'])->timezone('Europe/Moscow')->format('Y-m-d\TH:i:sP'):null,
            'places' => $this->getPlaces($this['placeIds']),
        ];
    }

    private function getPlaces(array $placeIds)
    {
       $places = Place::query()->whereIn('id',$placeIds)->get();
       return PlaceResource::collection($places);
    }
}
