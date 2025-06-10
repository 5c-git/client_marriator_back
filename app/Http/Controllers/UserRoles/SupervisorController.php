<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Enum\User\SortEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\GetTaskRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetUserDataRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\Order\TaskShortResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserResource;
use App\Models\Order\Task;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Resources\PlaceResource;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Resources\Order\TaskResource;
use App\Http\Requests\Order\AcceptTaskRequest;
use App\Http\Requests\Order\CreateBidFromOrderRequest;
use App\Http\Requests\Order\CreateBidFromTaskRequest;

class SupervisorController extends Controller
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

        if(!empty($request->status)){
            if(in_array($request->status,$arrRoleConfirm)){
                $arrRoleConfirm = [$request->status];
            }else{
                $arrRoleConfirm = [];
            }
        }

        $usersForModeration = $this->userRepository->getModerationUsersPaginate($arrRoleConfirm,
            SortEnum::from($request->input('sort',SortEnum::new->value)),
            UserStatusModerationEnum::from($request->input('status',UserStatusModerationEnum::new->value)),
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
                if($request->supervisorIds) {
                    $userForModeration->supervisors()->sync($request->supervisorIds);
                }
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

    public function getOrders(GetOrderRequest $request)
    {
        return ShortOrderResource::collection(
            $this->orderRepository->getOrderByUserSyncDataPaginate(
                $request->user(),
                OrderStatusEnum::notAccepted,
                $request->input('page', 1),
                $request->input('perPage', 10),
            )
        );
    }

    public function getOrder(GetOrderRequest $request): OrderResource
    {
        return new OrderResource(
            $this->orderRepository->getOrderByUserSyncData(
                $request->user(),
                $request->input('orderId',null)
            )
        );
    }

    public function acceptOrder(AcceptOrderRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptedOrder($user,$request->orderId)) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function getTasks(GetTaskRequest $request){
        return TaskShortResource::collection(
            $this->orderRepository->getTaskByUserSyncDataPaginate(
                $request->user(),
                OrderStatusEnum::from($request->input('status',3)),
                $request->input('page', 1),
                $request->input('perPage', 10),
            )
        );
    }

    public function getTask(GetTaskRequest $request){
        return new TaskResource(
            $this->orderRepository->getTaskByUserSyncData(
                $request->user(),
                $request->input('taskId',null)
            )
        );
    }

    public function acceptTask(AcceptTaskRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptTask($user,$request->taskId)) {
           Task::where('id',$request->taskId)->first()->acceptingUsers()->delete();
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function createBidFromOrder(CreateBidFromOrderRequest $request){
        $user = $request->user();
        $bid = $this->orderRepository->createBidFromOrder($user,$request->orderId,$request->orderActivityId);
    }

    public function createBidFromTask(CreateBidFromTaskRequest $request){
        $user = $request->user();
        $bid = $this->orderRepository->createBidFromTask($user,$request->taskId,$request->taskActivityId);

    }

}
