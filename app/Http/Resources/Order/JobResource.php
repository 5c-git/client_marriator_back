<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\AcceptingUsersResource;
use App\Http\Resources\Order\OrderActivitiesResource;
use App\Http\Resources\Order\StatisticResource;
use App\Http\Resources\ViewActivityResource;
use App\Models\Fields\Directory\Radius;
use App\Models\Order\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ShortUserResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin \App\Models\Order\Bid
 */
class JobResource extends JsonResource
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
            'status' => $this->status->value,
            'selfEmployed' => (bool)$this->self_employed,
            'place' => new PlaceResource($this->place),
            'radius' => $this->radius ?? $this->getRadius(),
            'price' => (float)($this->price ?? $this->getPrice()),
            'priceResult' => (float)($this->price ?? $this->getPrice())*($this->self_employed?0.94:0.87),
            'income' => 0,
            'forPay' => $this->acceptingUser ? $this->getForPay($this->getReports($this->acceptingUser)) : 0,
            'viewActivity' => new ViewActivityResource($this->viewActivity),
            'dateStart' => $this->date_start,
            'dateEnd' => $this->date_end,
            'needFoto' => (bool)$this->need_foto,
            'dateActivity' => DateActivityResource::collection(collect($this->date_activity)),
            'order' => new ShortOrderResource($this->order),
            'task' => new TaskShortResource($this->task),
            'acceptingUser' => new AcceptingUsersResource($this->acceptingUser),
            'reports' =>  $this->acceptingUser ? ReportResource::collection($this->getReports($this->acceptingUser)) : [],
        ];
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

    private function getReports(User $user)
    {
        return $user->reports()?->where('bid_id',$this->id)->get();
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

    private function getForPay($reports = null): float|int
    {
        $forPay = 0;
        if($reports) {
            foreach ($reports as $report) {
                /** @var  $report Report */
                $forPay += $report->forPay * ($report->coefficient ?? 1) - $report->getReasonsAmount();
            }
        }
        return $forPay;
    }
}
