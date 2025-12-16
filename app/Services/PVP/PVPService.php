<?php

namespace App\Services\PVP;

use App\Enum\Order\OrderStatusEnum;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
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
                    $newOrder                = new Order();
                    $newOrder->place_id      = $place->id;
                    $newOrder->external_id   = $item['externalId'];
                    $newOrder->user_id       = $item['userId'];
                    $newOrder->self_employed = $item['selfEmployed'];
                    $newOrder->status        = OrderStatusEnum::notAccepted->value;
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

    public function getPlace(string $prefix, string $place): ?Place
    {
        return Place::where('external_id',$prefix.$place)->first();
    }

    public function getJob(string $prefix, string $job): ?ViewActivities
    {
        return ViewActivities::where('external_id',$prefix.$job)->first();
    }



    private function saveData(array $orders){

    }

    static function getServiceObject($namePvp): ?self
    {
        if(class_exists($namePvp)) {
            return new self(new $namePvp());
        }
        return null;
    }

}
