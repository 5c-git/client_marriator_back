<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'project' => ProjectResource::collection($this->project),
            'place' => PlaceResource::collection($this->place),
            'roles' => RoleResource::collection($this->roles)
        ];
    }
}
