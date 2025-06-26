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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' =>  $this->img ? Storage::url($this->img) : null,
            'project' => ProjectResource::collection($this->project),
            'place' => PlaceResource::collection($this->place),
            'roles' => RoleResource::collection($this->roles),
            'change_order' => $this->change_order,
            'cancel_order' => $this->cancel_order,
            'live_order' => $this->live_order,
            'change_task' => $this->change_task,
            'cancel_task' => $this->cancel_task,
            'live_task' => $this->live_task,
            'repeat_bid' => $this->repeat_bid,
            'leave_bid' => $this->leave_bid,
            'refusal_task' => $this->refusal_task,
            'waiting_task' => $this->waiting_task,
            'supervisors' => ShortUserResource::collection($this->supervisors),
            'manager' => ShortUserResource::collection($this->manager)
        ];
    }
}
