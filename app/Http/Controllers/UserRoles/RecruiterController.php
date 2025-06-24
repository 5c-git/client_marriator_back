<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetUserDataRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Resources\PlaceResource;
use App\Http\Requests\Order\GetRequestsRequest;
use App\Http\Requests\Order\GetRequestRequest;
use App\Models\Order\Request;
use App\Http\Resources\Order\RequestResource;
use App\Http\Requests\Order\AcceptRequestRequest;

class RecruiterController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository,protected OrderRepository $orderRepository)
    {
    }

    public function getPlace(){
        $userPlace = Auth::user()->place;
        return PlaceResource::collection($userPlace);
    }

    public function setUserData(SetUserDataRequest $request): SuccessResource
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();
        return new SuccessResource();
    }

    public function getRequests(GetRequestsRequest $request)
    {
        $user = Auth::user();
        $userPlaceId = $user->place?->pluck('id')?->toArray();
        $status = OrderStatusEnum::from($request->input('status',3));
        return RequestResource::collection(Request::query()
            ->orWhere(function ($query) use ($user,$status) {
                $query = $query->where('accept_user_id', $user->id);
                $query->where('status', $status->value);
            })
            ->orWhere(function ($query) use ($status,$userPlaceId) {
                $query = $query->whereIn('place_id', $userPlaceId);
                $query->where('status', $status->value);
            })->get());
    }

    public function getRequest(GetRequestRequest $request): RequestResource
    {
        $user = Auth::user();
        $userPlaceId = $user->place?->pluck('id')?->toArray();
        $status = OrderStatusEnum::from($request->input('status',3));
        $requestOrderId = $request->requestId;
        return new RequestResource(Request::query()
            ->orWhere(function ($query) use ($user,$status,$requestOrderId) {
                $query->where('accept_user_id', $user->id)
                ->where('status', $status->value)
                ->where('id',$requestOrderId);
            })
            ->orWhere(function ($query) use ($user,$status,$userPlaceId,$requestOrderId) {
                $query->whereIn('place_id', $userPlaceId)
                    ->where('status', $status->value)
                    ->where('id',$requestOrderId);
            })->first());
    }

    public function acceptRequest(AcceptRequestRequest $request): SuccessResource|ErrorResource
    {
        $user = Auth::user();
        $updateStatus = (bool)Request::query()
            ->where('id',$request->requestId)
            ->update(
                [
                    'status'=>OrderStatusEnum::accepted->value,
                    'accept_user_id' => $user->id
                ]
            );

        if($updateStatus) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }
}
