<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\AcceptSpecialistRequest;
use App\Http\Requests\Order\AcceptTaskRequest;
use App\Http\Requests\Order\CreateBidFromOrderRequest;
use App\Http\Requests\Order\CreateBidFromTaskRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\GetPlaceForBidRequest;
use App\Http\Requests\Order\GetViewActivitiesForOrderRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Requests\SetUserDataRequest;
use App\Http\Requests\UserData\DelManagerRequest;
use App\Http\Requests\UserData\GetManagerRequest;
use App\Http\Requests\UserData\SetManagerRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\BidResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ShortOrderResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RadiusResponse;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ViewActivityResource;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Radius;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use App\Models\Order\Bid;
use App\Models\Order\Order;
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
use App\Http\Requests\UserData\UserModerationRequest;
use App\Http\Requests\UserData\DelProjectRequest;
use App\Http\Requests\UserData\GetProjectRequest;
use App\Http\Requests\UserData\SetProjectRequest;
use App\Http\Requests\UserData\SetPlaceRequest as SetPlaceModerationRequest;
use App\Http\Requests\UserData\DelPlaceRequest as DelPlaceModerationRequest;
use App\Http\Requests\UserData\GetPlaceRequest;
use App\Http\Requests\Order\EntrustBidRequest;
use App\Http\Requests\Order\AcceptBidRequest;
use App\Http\Requests\Order\CancelBidRequest;
use App\Http\Requests\Order\GetSpecialistForBisRequest;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\UserData\SetUserImgRequest;
use App\Http\Requests\Order\CreateTaskActivityRequest;
use App\Http\Requests\Order\UpdateTaskRequest;
use App\Http\Requests\Order\GetViewActivitiesForTaskRequest;
use App\Http\Requests\Order\DeleteTaskActivityRequest;
use App\Http\Requests\Order\CreateRequestFromTaskRequest;
use App\Http\Requests\Order\CreateRequestFromBidRequest;
use App\Http\Requests\Order\CancelRequestRequest;
use App\Http\Resources\Order\RequestResource;
use App\Http\Requests\UserData\GetClientRequest;
use App\Http\Requests\UserData\GetSurepvisorRequest;
use App\Http\Requests\UserData\SetSurepvisorsRequest;
use App\Http\Requests\UserData\DelSurepvisorRequest;
use App\Models\Fields\Directory\Counterparty;
use App\Http\Resources\CounterpartyResource;
use App\Http\Requests\UserData\SetCounterpartyRequest;
use App\Http\Requests\UserData\DeleteCounterpartyRequest;
use App\Http\Requests\Order\InstructTaskRequest;
use App\Http\Requests\Order\GetProjectForTaskRequest;
use App\Http\Requests\Order\GetSurepvisorDataRequest;
use App\Http\Resources\Order\BidShortResource;
use App\Http\Requests\Order\RepeatTaskRequest;
use App\Http\Requests\Order\UpdateTaskActivityRequest;

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

    public function getCounterparty(){
        return CounterpartyResource::collection(Counterparty::get());
    }

    public function setCounterparty(SetCounterpartyRequest $request)
    {
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
            $user->counterparty()->syncWithoutDetaching($request->counterpartyIds);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function deleteCounterparty(DeleteCounterpartyRequest $request){
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
            $user->counterparty()->detach($request->counterpartyId);

            $user = User::where('id',$request->userId)->first();
            $projectsForCounterparty = $user->counterparty
                ->flatMap(fn($counterparty) => $counterparty->projects)
                ->unique('id')->pluck('id')->toArray();

            $projectUser = $user->project?->pluck('id')->toArray();
            $result = array_diff($projectUser, $projectsForCounterparty);
            if($result) {
                $user->project()->detach($result);
            }

            $user = User::where('id',$request->userId)->first();
            $placesProject = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id')?->pluck('id')->toArray();
            $places = $user->place?->pluck('id')->toArray();
            $result = array_diff($places, $placesProject);
            if($result) {
                $user->place()->detach($result);
            }

            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
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
            $projects = $user->counterparty
                ->flatMap(fn($counterparty) => $counterparty->projects)
                ->unique('id');
            return ProjectResource::collection($projects);
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
            $projects = $user->counterparty
                ->flatMap(fn($counterparty) => $counterparty->projects)
                ->unique('id')->pluck('id')->toArray();
            $projectIds = [];
            foreach ($request->projectId as $projectOneId){
                if(in_array($projectOneId,$projects)){
                    $projectIds[] = $projectOneId;
                }
            }
            $user->project()->syncWithoutDetaching($projectIds);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function setUserImg(SetUserImgRequest $request){
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
            $project = Project::where('id',$request->projectId)->first();
            $projectLogo = $project?->brands()?->first()?->logo;
            $user->img = $projectLogo;
            $user->save();
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

    public function getUserSurepvisorData(GetSurepvisorRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value])){
                $checkRole = true;
                break;
            }
        }
        $supervisorUsers = collect();
        if($checkRole) {
            $user = $request->user();
            $supervisorUsers = $user->supervisors;
        }
        return ShortUserResource::collection($supervisorUsers);
    }

    public function getSurepvisors(GetSurepvisorRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value])){
                $checkRole = true;
                break;
            }
        }
        $users = collect();
        if($checkRole) {
            $deleteSuperVisor = $user->supervisors?->pluck('id')->toArray();
            $users = User::whereHas('roles', function ($query) {
                $query->where('role_id', RoleEnum::supervisor->value);
            });
            if($deleteSuperVisor) {
                $users = $users->whereNotIn('id', $deleteSuperVisor);
            }
            $users = $users->where('confirmRegister', true)->where('finishRegister', true)->get();
        }
        return ShortUserResource::collection($users);
    }

    public function getManager(GetManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::supervisor->value])){
                $checkRole = true;
                break;
            }
        }
        $users = collect();
        if($checkRole) {
            $deleteManager = $user->manager?->pluck('id')->toArray();
            $users = User::whereHas('roles', function ($query) {
                $query->where('role_id', RoleEnum::manager->value);
            });
            if($deleteManager) {
                $users = $users->whereNotIn('id', $deleteManager);
            }
            $users = $users->where('confirmRegister', true)->where('finishRegister', true)->get();
        }
        return ShortUserResource::collection($users);
    }

    public function setSurepvisors(SetSurepvisorsRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole) {
            $user->supervisors()->syncWithoutDetaching($request->surepvisorIds);
            return new SuccessResource();
        }
        return new ErrorResource();
    }

    public function setManagers(SetManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::supervisor->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole) {
            $user->manager()->syncWithoutDetaching($request->managerIds);
            return new SuccessResource();
        }
        return new ErrorResource();
    }

    public function delSurepvisor(DelSurepvisorRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::manager->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole) {
            $user->supervisors()->detach($request->surepvisorId);
            return new SuccessResource();
        }
        return new ErrorResource();
    }

    public function delManager(DelManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $checkRole = false;
        foreach ($userRoles as $userRole){
            if(in_array($userRole,[RoleEnum::supervisor->value])){
                $checkRole = true;
                break;
            }
        }
        if($checkRole) {
            $user->manager()->detach($request->managerId);
            return new SuccessResource();
        }
        return new ErrorResource();
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

        if(!empty($request->role)){
            if(in_array($request->role,$arrRoleConfirm)){
                $arrRoleConfirm = [$request->role];
            }else{
                $arrRoleConfirm = [];
            }
        }

        $usersForModeration = $this->userRepository->getModerationUsersPaginate($arrRoleConfirm,
            SortEnum::from($request->input('sort',SortEnum::new->value)),
            $request->input('status') ? UserStatusModerationEnum::from($request->input('status')) : null,
            $request->input('page', 1),
            $request->input('perPage', 10),
        );

        return UserResource::collection($usersForModeration);
    }

    public function getModerationSingleClient(GetClientRequest $request): UserResource
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

        $usersForModeration = $this->userRepository->getModerationUser(
            $request->userId,
            $arrRoleConfirm
        );

        return new UserResource($usersForModeration);
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
            $userForModeration->change_order = $request->change_order ?? null;
            $userForModeration->cancel_order = $request->cancel_order ?? null;
            $userForModeration->live_order = $request->live_order ?? null;
            $userForModeration->change_task = $request->change_task ?? null;
            $userForModeration->cancel_task = $request->cancel_task ?? null;
            $userForModeration->live_task = $request->live_task ?? null;
            $userForModeration->repeat_bid = $request->repeat_bid ?? null;
            $userForModeration->leave_bid = $request->leave_bid ?? null;
            $userForModeration->refusal_task = $request->refusal_task ?? null;
            $userForModeration->waiting_task = $request->waiting_task ?? null;
            $userForModeration->count_wait_bid = $request->count_wait_bid ?? null;
            $userForModeration->time_answer_bid = $request->time_answer_bid ?? null;
            $userForModeration->notification_start = $request->notification_start ?? null;

            if($request->phone) {
                $userForModeration->phone = $request->phone;
            }
            if($request->name) {
                $userForModeration->name = $request->name;
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
        return OrderResource::collection(
            $this->orderRepository->getOrderByUserSyncDataPaginate(
                $request->user(),
                $request->input('status') ? OrderStatusEnum::from($request->input('status')) : null
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

    public function getTasks(GetTaskRequest $request){
        return TaskResource::collection(
            $this->orderRepository->getTaskByUserSyncDataPaginate(
                $request->user(),
                $request->input('status') ? OrderStatusEnum::from($request->input('status')) : null
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

    public function createTaskActivity(CreateTaskActivityRequest $request): TaskResource
    {
        return new TaskResource(
            $this->orderRepository->createTaskActivity(
                $request
            )
        );
    }

    public function updateTaskActivity(UpdateTaskActivityRequest $request): TaskResource
    {
        return new TaskResource(
            $this->orderRepository->updateTaskActivity(
                $request
            )
        );
    }

    public function deleteTaskActivity(DeleteTaskActivityRequest $request)
    {
        return new TaskResource(
            $this->orderRepository->deleteTaskActivity(
                $request
            )
        );
    }

    public function updateTask(UpdateTaskRequest $request): TaskResource
    {
        return new TaskResource($this->orderRepository->updateTask($request));
    }

    public function repeatTask(RepeatTaskRequest $request): TaskResource
    {
        return new TaskResource($this->orderRepository->repeatTask($request));
    }

    public function instructTask(InstructTaskRequest $request): ErrorResource|SuccessResource
    {
        if($request->taskId){
            $this->orderRepository->instructTask($request->taskId,$request->input('supervisorId')?[$request->input('supervisorId')]:[]);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function getSurepvisorData(GetSurepvisorDataRequest $request){
        $user = $request->user();
        $supervisorUsers = $user->supervisors;
        return ShortUserResource::collection($supervisorUsers);
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
            Task::where('id',$request->taskId)->first()->acceptingUsers()->detach();
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function createBidFromOrder(CreateBidFromOrderRequest $request){
        $user = $request->user();
        return new BidResource(
            $this->orderRepository->createBidFromOrder(
                $user,
                $request->orderId,
                $request->orderActivityId
            )
        );
    }

    public function createBidFromTask(CreateBidFromTaskRequest $request){
        $user = $request->user();
        return new BidResource(
            $this->orderRepository->createBidFromTask(
                $user,
                $request->taskId,
                $request->taskActivityId
            )
        );
    }

    public function getViewActivitiesForTask(GetViewActivitiesForTaskRequest $request){
        $task = Task::where('id',$request->taskId)->first();
        $viewActivities = $task->project->viewActivities
            ->unique('id');
        $viewActivities = $viewActivities->where('self_employed', $task->self_employed);
        return ViewActivityResource::collection($viewActivities);
    }

    public function getBids(GetBidsRequest $request)
    {
        return BidShortResource::collection(
            $this->orderRepository->getBidsByUserSyncDataPaginate(
                $request->user(),
                $request->input('status') ? OrderStatusEnum::from($request->input('status')) : null
            )
        );
    }

    public function getBid(GetBidRequest $request)
    {
        return new BidResource(
            $this->orderRepository->getBidByUserSyncData(
                $request->user(),
                $request->input('bidId',null)
            )
        );
    }

    public function invoiceBid(EntrustBidRequest $request): ErrorResource|SuccessResource
    {
        if($request->bidId){
            $this->orderRepository->invoiceBid($request->bidId,$request->input('specialistIds',[]));
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function acceptBid(AcceptBidRequest $request): ErrorResource|SuccessResource
    {
        $user = $request->user();
        if($this->orderRepository->acceptBid($user,$request->bidId)) {
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function instructBid(EntrustBidRequest $request): ErrorResource|SuccessResource
    {
        if($request->bidId){
            $this->orderRepository->instructBid($request->bidId,$request->input('supervisorId'));
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function cancelBid(CancelBidRequest $request): ErrorResource|SuccessResource
    {
        if($request->bidId){
            $this->orderRepository->cancelBid($request->bidId);
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
    }

    public function getSpecialistForBid(GetSpecialistForBisRequest $request){
        return ShortUserResource::collection($this->orderRepository->getSpecialistForBid($request->bidId));
    }

    public function updateBid(BidDataRequest $request){
        return new BidResource($this->orderRepository->updateBid($request));
    }

    public function createRequestFromTask(CreateRequestFromTaskRequest $request): RequestResource
    {
        $user = $request->user();
        return new RequestResource($this->orderRepository->createRequestFromTask($request,$user));
    }

    public function createRequestFromBid(CreateRequestFromBidRequest $request): RequestResource
    {
        $user = $request->user();
        return new RequestResource($this->orderRepository->createRequestFromBid($request,$user));
    }

    public function cancelRequest(CancelRequestRequest $request): SuccessResource|ErrorResource
    {
        if($this->orderRepository->cancelRequest($request)){
            return new SuccessResource();
        }
        return new ErrorResource();
    }

    public function getProjectForTask(GetProjectForTaskRequest $request)
    {
        $user     = Auth::user();
        $projects = $user->project()
            ->whereHas('places', function ($query) use ($request) {
                $query->where('directory_place.id', $request->placeId);
            })
            ->get();
        return ProjectResource::collection($projects);
    }

    public function getPlaceForBid()
    {
        $places = Auth::user()->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
    }

    public function getRadiusSelect()
    {
        $radius = Radius::get();
        return RadiusResponse::collection($radius);
    }

    public function acceptSpecialist(AcceptSpecialistRequest $request)
    {
        $bid = Bid::where('id',$request->bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot($request->specialistId, [
            'accepted' => BidAcceptingStatusEnum::accepted->value,
        ]);
    }

    public function declinedSpecialist(AcceptSpecialistRequest $request)
    {
        $bid = Bid::where('id',$request->bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot($request->specialistId, [
            'accepted' => BidAcceptingStatusEnum::declined->value,
        ]);
    }

}
