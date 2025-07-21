<?php

namespace App\Http\Resources;

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
class ViewActivityResource extends JsonResource
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
            'detailName' => $this->detail_name,
            'previewText' => $this->preview_text,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'traveling' =>  (bool)$this->traveling
        ];

    }
}
