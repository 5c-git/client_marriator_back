<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;
use App\Http\Requests\Log\LogRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Traits\Log\Loggable;


class LogController extends Controller
{
    use Loggable;

    public function add(LogRequest $request): SuccessResource|ErrorResource
    {
        try {
            $this->logFrontendError($request);
            return new SuccessResource();
        }catch (\Throwable){
            return new ErrorResource();
        }
    }

}
