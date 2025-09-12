<?php

namespace App\Http\Resources;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RoleResource;
use App\Models\Fields\Directory\Age;
use App\Models\Fields\Directory\Citizenship;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\User
 */
class AcceptingUsersResource extends JsonResource
{
    private array $moreInfo = [];
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
            'roles' => RoleResource::collection($this->roles),
            'radius' => $this->mapRadius,
            'status' => $this->pivot->accepted
        ];
    }

    private function getMoreInformation()
    {
        if(!$this->moreInfo) {
            $this->moreInfo['fieldView'] = Fields::where('directory', ViewActivities::class)->first();
            $this->moreInfo['fieldCiti'] = Fields::where('directory', Citizenship::class)->first();
            $this->moreInfo['fieldAge']  = Fields::where('directory', Age::class)->first();
        }
    }
}
