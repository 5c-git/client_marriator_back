<?php

namespace App\Http\Resources\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Http\Resources\AcceptingUsersResource;
use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\Order\StatisticResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ViewActivityResource;
use App\Models\Fields\Directory\Radius;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \App\Models\Order\SearchRequest
 */
class SearchResource extends JsonResource
{
    private int $radiusDefault = 5;
    private int $radiusBd = 0;
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
            'selfEmployed' => (bool)$this->self_employed,
            'place' => new PlaceResource($this->place),
            'radius' => $this->radius ?? $this->getRadius(),
            'price' => (int)(($this->price ?? $this->getPrice())),
            'priceResult' => (int)(($this->price ?? $this->getPrice())),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => DateActivityResource::collection(collect($this->date_activity)),
            'order' => new ShortOrderResource($this->order),
            'task' => new TaskShortResource($this->task),
            'count' => $this->count,
            'project'=> new ProjectResource($this->getProject()),
        ];
    }

    private function getPrice()
    {
        $project = null;
        if($this->order){
            $project = $this->order->user?->project?->first();
        }elseif($this->task){
            $project = $this->task?->project;
        }
        $price = 0;
        if($project) {
            foreach ($project->viewActivities as $viewActivity) {
                if ($viewActivity->id == $this->view_activity_id) {
                    $price = $viewActivity->pivot->price;
                }
            }
        }
        return $price;
    }

    private function getProject(){
        return $this->order?->user?->project?->first()
            ?? $this->task?->project
            ?? $this->task?->order?->user?->project?->first();
    }

    private function getRadius()
    {
        if(!$this->radiusBd){
            $radius = Radius::where('default',true)->first();
            if(!$radius) {
                $this->radiusBd = $this->radiusDefault;
            }else{
                $this->radiusBd = $radius->value;
            }
        }
        return $this->radiusBd;
    }
}
