<?php

namespace App\Http\Controllers\UserRoles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\CommonBidResource;
use App\Models\Order\Bid;
use Illuminate\Http\Request;
use App\Services\Local\Repositories\Contracts\UserRepository;

class CommonController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getBids()
    {
        $bids = Bid::query()
            ->where('self_employed',true)
            ->orderBy('date_start','desc')
            ->limit(100)
            ->get();
        return CommonBidResource::collection($bids);
    }

}
