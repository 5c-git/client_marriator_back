<?php

namespace App\Http\Resources;

use App\Http\Resources\Order\StandardResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Fields\Directory\ViewActivities;

/**
 * @mixin ViewActivities
 */
class ViewActivityResourceJob extends JsonResource
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
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'previewText' => $this->preview_text,
            'detailName' => $this->detail_name,
            'detailText' => $this->detail_text,
            'detailImg' => $this->detail_img ? Storage::url($this->detail_img) : null,
            'linkText' => $this->link_text,
            'link' => $this->link,
            'traveling' =>  (bool)$this->traveling,
            'standard' => $this->standardDirectory?new StandardResource($this->standardDirectory):null
        ];

    }
}
