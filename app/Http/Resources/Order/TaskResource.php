<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\ProjectResource;
use App\Http\Resources\Order\StatisticResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use \App\Http\Resources\Order\OrderActivitiesResource;


/**
 * @mixin \App\Models\Order\Task
 */
class TaskResource extends JsonResource
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
            'selfEmployed' => (bool)$this->self_employed,
            'status' => $this->status->value,
            'place' => new PlaceResource($this->place),
            'project'=> $this->project ? new ProjectResource($this->project) : new ProjectResource($this->order->user->project->first()),
            'user' => new ShortUserResource($this->user),
            'acceptUser' => new ShortUserResource($this->acceptUser),
            //'price' => $this->price,
            //'priceResult' => $this->price*($this->self_employed?0.94:0.87),
            //'income' => $this->income,
            //'scopeOfServices' => $this->scope_of_services,
            'orderActivities' => OrderActivitiesResource::collection($this->taskActivities),
            'acceptedUser' => ShortUserResource::collection($this->acceptingUsers),
            'statistic' => $this->getStatistic()
        ];
    }

    private function getStatistic(){
        $statusCounts = DB::table('accept_bid')
            ->where('task_id', $this->id)
            ->groupBy('accepted')
            ->select('accepted', DB::raw('COUNT(*) as count'))
            ->get();
        return StatisticResource::collection($statusCounts);
    }
}
