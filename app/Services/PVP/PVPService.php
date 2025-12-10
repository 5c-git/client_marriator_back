<?php

namespace App\Services\PVP;

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
