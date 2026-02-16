<?php

namespace App\Services\PVP;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Report;
use App\Models\User;
use App\Services\PVP\TimeBook\TimeBookService;
use App\Services\PVP\Verme\VermeService;
use App\Services\PVP\XFive\XFiveService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PVPService
{
    public function __construct(private PVPAbstract $pvp)
    {

    }

    private function get(){
        return $this->pvp->getData();
    }

    public function startLoad(){
        $loadDataPvp = $this->get();
        $prefix = $this->pvp->getPrefix();
        foreach ($loadDataPvp as $item) {
            if(!Order::query()->where('external_id',$item['externalId'])->exists()) {
                $place = $this->getPlace($prefix, (string)$item["place"]);
                $job   = $this->getJob($prefix, (string)$item["job"]);


                if (!empty($place) && !empty($job)) {
                    $user = $this->getUser($place);
                    $newOrder                = new Order();
                    $newOrder->place_id      = $place->id;
                    $newOrder->external_id   = $item['externalId'];
                    $newOrder->user_id       = $user->id;
                    $newOrder->self_employed = $item['selfEmployed'];
                    $newOrder->status        = OrderStatusEnum::notAccepted->value;
                    $newOrder->external_type = $this->pvp->getType();
                    $newOrder->save();


                    $orderActivity = new OrderActivities([
                        'view_activity_id' => $job->id,
                        'count'            => 1,
                        'date_start'       => $item['dateStart'],
                        'date_end'         => $item['end'],
                        'need_foto'        => true,
                        'date_activity'    => [],
                        'order_id'         => $newOrder->id
                    ]);
                    $orderActivity->save();
                }
            }
        }
    }

    public function getResultData(User $user,Bid $bid)
    {
       $data = $this->pvp->getTimesheets($user,$bid);
    }

    public function getUser(Place $place): User
    {
         $project = $place->project?->first();
         if($project){
             $user = User::whereHas('project', function ($query) use ($project) {
                 $query->where('directory_project.id', $project->id);
             })
                 ->whereHas('roles', function ($query) {
                     $query->where('name', RoleEnum::client->name);
                 })
                 ->first();
         }else{
             $user = User::whereHas('roles', function ($query) {
                     $query->where('name', RoleEnum::client->name);
                 })
                 ->first();
         }
         return $user;
    }

    public function getPlace(string $prefix, string $place): ?Place
    {
        return Place::where('external_id',$prefix.$place)->first();
    }

    public function getJob(string $prefix, string $job): ?ViewActivities
    {
        return ViewActivities::where('external_id',$prefix.$job)->first();
    }

    public function assignToShift(User $user,string $guid)
    {
        return $this->pvp->assignToShift( $user, $guid);
    }

    private function saveData(array $orders){

    }

    public function getDataWork(User $user,Bid $bid)
    {
        return $this->pvp->getTimesheets($user,$bid);
    }

    static function getServiceObject($namePvp): ?self
    {
        if(class_exists($namePvp)) {
            return new self(new $namePvp());
        }
        return null;
    }

    static function getObj(int $type): self
    {
        $pvp = match ($type) {
            VermeService::getType() => new VermeService(),
            XFiveService::getType() => new XFiveService(),
            TimeBookService::getType() => new TimeBookService(),
        };
        return new self($pvp);
    }

    static function getResultWork(Report $report)
    {
       $bid = $report->bid;
       if(!empty($bid->external_type)){
           $pvpService = self::getObj($bid->external_type);
           $dataWork = $pvpService->getDataWork($report->user,$bid);
           if($dataWork){
               $report->hours = $dataWork;
               $report->pvp = false;
               $report->forPay = self::getPriceForHour($report);
           }
       }else{
           $report->pvp = false;
           $report->save();
       }
       $report->save();

    }

    static function getPriceForHour(Report $report): float
    {
        if ($report->order) {
            $project = $report->order->user->project->first();
        } elseif ($report->task) {
            $project = $report->task->project;
        }
        if(!$report->bid->price) {
            $price = 0;
            foreach ($project->viewActivities as $viewActivity) {
                if ($viewActivity->id === $report->bid->view_activity_id) {
                    $price = $viewActivity->pivot->price;
                    break;
                }
            }
            $price = $price * $report->hours;
        }else{
            $price = $report->bid->price * $report->hours;
        }
        return $price;
    }

}
