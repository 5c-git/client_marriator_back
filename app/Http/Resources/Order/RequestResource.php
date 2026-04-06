<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\ViewActivityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;

/**
 * @mixin \App\Models\Order\Request
 */
class RequestResource extends JsonResource
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
            'user' => new ShortUserResource($this->user),
            'acceptUserId' => new ShortUserResource($this->acceptUser),
            'status' => $this->status->getStatusName(),
            'selfEmployed' => (bool)$this->self_employed,
            'radius' => $this->radius,
            'price' => (int)(($this->price ?? $this->getPrice())),
            'priceResult' => (int)(($this->price ?? $this->getPrice())),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'count' => $this->count,
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => $this->date_activity,
        ];
    }

    private function getPrice()
    {
        if($this->order){
            $project = $this->order->user->project->first();
        }elseif($this->task){
            $project = $this->task->project;
        }
        $price = 0;
        foreach ($project->viewActivities as $viewActivity){
            if($viewActivity->id == $this->view_activity_id){
                $price = $viewActivity->pivot->price;
            }
        }
        return $price;
    }
}
