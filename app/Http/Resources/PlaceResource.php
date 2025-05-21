<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BrandResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Fields\Directory\Place
 */
class PlaceResource extends JsonResource
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
        $logo = $this->project()?->first()?->brands()?->first()?->logo;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address_kladr' => $this->address_kladr,
            'logo' => $logo ? Storage::url($logo) : null
        ];
    }
}
