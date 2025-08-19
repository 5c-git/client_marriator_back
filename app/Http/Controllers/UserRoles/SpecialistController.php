<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AcceptBidRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Order\Bid;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Requests\Order\RejectBidRequest;

class SpecialistController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository,protected OrderRepository $orderRepository)
    {
    }

    public function getBids(GetBidsRequest $request)
    {
        return ShortOrderResource::collection(
            $this->orderRepository->getBidsByUserSyncDataPaginate(
                $request->user(),
                $request->input('status') ? OrderStatusEnum::from($request->input('status')) : null
            )
        );
    }

    public function getBid(GetBidRequest $request)
    {
        return new OrderResource(
            $this->orderRepository->getBidByUserSyncData(
                $request->user(),
                $request->input('bidId',null)
            )
        );
    }

    public function acceptBid(AcceptBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptBid($user,$request->bidId)) {
            Bid::where('id',$request->bidId)->first()->acceptingUsers()->detach();
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function rejectBid(RejectBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->rejectBid($user,$request->bidId)) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function startDay(StartDayRequest $request)
    {

    }

    public function endDay(EndDayRequest $request)
    {

    }

    public function reportBid(ReportRequest $request)
    {

    }




}
