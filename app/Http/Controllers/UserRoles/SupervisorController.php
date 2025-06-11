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
use App\Http\Requests\UserData\DelPlaceRequest as DelPlaceModerationRequest;
use App\Http\Requests\UserData\DelProjectRequest;
use App\Http\Requests\UserData\GetPlaceRequest;
use App\Http\Requests\UserData\GetProjectRequest;
use App\Http\Requests\UserData\SetPlaceRequest as SetPlaceModerationRequest;
use App\Http\Requests\UserData\SetProjectRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\Order\TaskShortResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserResource;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
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

    public function getProject(GetProjectRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole){
            $project = Project::all();
            return ProjectResource::collection($project);
        }else{
            return new ErrorResource();
        }
    }

    public function setProject(SetProjectRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole){
            $user->project()->syncWithoutDetaching($request->projectId);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function delProject(DelProjectRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole){
            $user->project()->detach($request->projectId);
            $user = User::where('id',$request->userId)->first();
            $placesProject = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id')?->pluck('id')->toArray();
            $places = $user->place?->pluck('id')->toArray();
            $result = array_diff($places, $placesProject);
            if($result) {
                $user->place()->detach($result);
            }
            return new UserResource($user->fresh());
        }else{
            return new ErrorResource();
        }
    }

    public function getPlaceModeration(GetPlaceRequest $request)
    {
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        if(in_array($userRoles[0],[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])){
            $places = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id');
            return PlaceResource::collection($places);
        }
        if($userRoles[0] == RoleEnum::recruiter->value){
            $places = Place::all();
            return PlaceResource::collection($places);
        }
        return new ErrorResource();
    }

    public function setPlaceModeration(SetPlaceModerationRequest $request): SuccessResource
    {
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $placeForUser = [];
        if(in_array($userRoles[0],[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])) {
            $placesProject = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id')?->pluck('id')->toArray();
            foreach ($request->placeId as $place) {
                if (in_array($place, $placesProject)) {
                    $placeForUser[] = $place;
                }
            }
        }
        if($userRoles[0] == RoleEnum::recruiter->value){
            $placeForUser = $request->placeId;
        }
        if($placeForUser){
            $user->place()->syncWithoutDetaching($placeForUser);
        }
        return new SuccessResource();
    }

    public function delPlaceModeration(DelPlaceModerationRequest $request): SuccessResource
    {
        $user = User::where('id',$request->userId)->first();
        $user->place()->detach($request->placeId);
        return new SuccessResource();
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
