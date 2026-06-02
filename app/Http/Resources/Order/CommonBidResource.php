<?php

namespace App\Http\Resources\Order;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\Order\StatisticResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ViewActivityResource;
use App\Models\Fields\Directory\Radius;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;

/**
 * @mixin \App\Models\Order\Bid
 */
class CommonBidResource extends JsonResource
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
            'place' => new PlaceResource($this->place),
            'price' => (int)(($this->price ?? $this->getPrice())),
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'project'=> new ProjectResource($this->getProject()),
        ];
    }

    private function getPrice()
    {
        $project = null;
        if($this->order){
            $project = $this->order->user?->project?->where('date_end','>=',Carbon::now())->first();
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
        return $this->order?->user?->project?->where('date_end','>=',Carbon::now())->first()
            ?? $this->task?->project
            ?? $this->task?->order?->user?->project?->first();
    }
}
