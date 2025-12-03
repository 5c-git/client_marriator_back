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

    public function get(){
        $this->pvp->get();
    }

}
