<?php

namespace App\Services\PVP;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class PVPAbstract
{
    public function __construct()
    {

    }

    abstract public function getData();
    abstract public function getPrefix():string;
    abstract public function getDefaultUserId():int;
    abstract protected function dataFormater($data): array;

}
