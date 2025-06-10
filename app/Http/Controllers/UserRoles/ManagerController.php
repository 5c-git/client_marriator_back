<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\AcceptTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Requests\SetUserDataRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Models\Fields\Fields;
use App\Models\Order\Task;
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
use App\Http\Requests\Order\ConvertTaskRequest;
use App\Http\Resources\Order\TaskResource;
use App\Http\Resources\ShortUserResource;
use App\Http\Requests\Order\GetTaskRequest;
use App\Http\Resources\Order\TaskShortResource;
use App\Http\Requests\Order\CreateTaskRequest;
use App\Http\Requests\Order\EntrustTaskRequest;
use App\Http\Requests\Order\CancelTaskRequest;
use App\Enum\User\SortEnum;

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
        $user->place()->detach($request->placeId);
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

    public function setUserData(SetUserDataRequest $request): SuccessResource
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();
        return new SuccessResource();
    }

    public function getOrders(GetOrderRequest $request)
    {
        return ShortOrderResource::collection(
            $this->orderRepository->getOrderByUserSyncDataPaginate(
                $request->user(),
                OrderStatusEnum::from($request->input('status',2)),
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

    public function convertTask(ConvertTaskRequest $request): ErrorResource|TaskResource
    {
        $user = $request->user();
        if($task = $this->orderRepository->convertTask($user,$request)) {
            return new TaskResource($task);
        }else{
            return new ErrorResource();
        }
    }

    public function getSurepvisorData(Request $request){
        $user = $request->user();
        $supervisorUsers = $user->supervisors;
        return ShortUserResource::collection($supervisorUsers);
    }

    public function getTasks(GetTaskRequest $request){
        return TaskShortResource::collection(
            $this->orderRepository->getTaskByUserSyncDataPaginate(
                $request->user(),
                OrderStatusEnum::from($request->input('status',2)),
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

    public function createTask(CreateTaskRequest $request): TaskResource
    {
        return new TaskResource(
            $this->orderRepository->createTask(
                $request,
                Auth::user()->id
            )
        );
    }

    public function updateTask(CreateTaskRequest $request): ErrorResource|TaskResource
    {
        if($request->taskId){
            return new TaskResource($this->orderRepository->updateTask($request));
        }else{
            return new ErrorResource();
        }
    }

    public function instructTask(EntrustTaskRequest $request): ErrorResource|SuccessResource
    {
        if($request->taskId){
            $this->orderRepository->instructTask($request->taskId,$request->input('supervisorIds',[]));
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function invoiceTask(EntrustTaskRequest $request): ErrorResource|SuccessResource
    {
        if($request->taskId){
            $this->orderRepository->invoiceTask($request->taskId,$request->input('supervisorIds',[]));
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function cancelTask(CancelTaskRequest $request): ErrorResource|SuccessResource
    {
        if($request->taskId){
            $this->orderRepository->cancelTask($request->taskId);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
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





}
