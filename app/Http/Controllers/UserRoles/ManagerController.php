<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Resources\UserResource;

class ManagerController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository,protected OrderRepository $orderRepository)
    {
    }

    public function getModerationClient(PaginatorRequest $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $arrRoleConfirm = [];
        foreach ($userRoles as $role){
            $arrRoleConfirm = RoleEnum::from($role)->getClientForModeration();
        }
        $arrRoleConfirm = array_unique($arrRoleConfirm);

        $usersForModeration = $this->userRepository->getModerationUsersPaginate($arrRoleConfirm,
            $request->input('page', 1),
            $request->input('perPage', 10),
        );

        return UserResource::collection($usersForModeration);
    }

    public function confirmUserRegister(ConfirmUserRequest $request): SuccessResource
    {
        $user = Auth::user();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $arrRoleConfirm = [];
        foreach ($userRoles as $role){
            $arrRoleConfirm = RoleEnum::from($role)->getClientForModeration();
        }
        $arrRoleConfirm = array_unique($arrRoleConfirm);

        $userForModeration = $this->userRepository
            ->getModerationUsers($arrRoleConfirm)?->where('id',$request->userId)?->first();
        if(!empty($userForModeration)){
            if($request->confirm){
                $userForModeration->confirmRegister = true;
            }else{
                $userForModeration->finishRegister = false;
            }
            $userForModeration->save();
        }

        return new SuccessResource();
    }


    public function getPlace()
    {
        $places = Auth::user()->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
    }

    public function setPlace(SetPlaceRequest $request): SuccessResource
    {
        $user = Auth::user();
        $place = $user->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id')->whereIn('id',$request->placeId)->pluck('id')?->toArray();
        if(!empty($place)) {
            $user->place()->sync($place);
            $user->save();
        }
        return new SuccessResource();
    }

    public function delPlace(DelPlaceRequest $request): SuccessResource
    {
        $user = Auth::user();
        $user->place()->detach([$request->placeId]);
        return new SuccessResource();
    }

    public function getBrand()
    {
        $brands = Auth::user()->project
            ->flatMap(fn($project) => $project->brands)
            ->unique('id');
        return BrandResource::collection($brands);
    }

    public function setBrandImg(SetBrandImgRequest $request)
    {
        $user = Auth::user();
        $brands = Auth::user()->project
            ->flatMap(fn($project) => $project->brands)
            ->unique('id')?->where('id',$request->brandId)?->first();

        if(!empty($brands)){
            $user->img = $brands->logo;
            $user->save();
        }
        return new SuccessResource();
    }

    public function getOrders(GetOrderRequest $request)
    {
        return ShortOrderResource::collection(
            $this->orderRepository->getOrderByUserSyncData(
                $request->user(),
                OrderStatusEnum::notAccepted,
                $request->input('page', 1),
                $request->input('perPage', 10),
            )
        );
    }

    public function acceptOrder(AcceptOrderRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptedOrder($user,$request->orderId)) {
            $user->acceptOrder()->syncWithoutDetaching([$request->orderId]);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function convertTask(AcceptOrderRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptedOrder($user,$request->orderId)) {
            $user->acceptOrder()->syncWithoutDetaching([$request->orderId]);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

}
