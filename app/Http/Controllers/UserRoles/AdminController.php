<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\User\SortEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\DelManagerRequest;
use App\Http\Requests\Order\GetManagerRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\SetManagerRequest;
use App\Http\Requests\UserData\DeleteCounterpartyRequest;
use App\Http\Requests\UserData\DelPlaceRequest as DelPlaceModerationRequest;
use App\Http\Requests\UserData\DelProjectRequest;
use App\Http\Requests\UserData\DelSurepvisorRequest;
use App\Http\Requests\UserData\GetClientRequest;
use App\Http\Requests\UserData\GetPlaceRequest;
use App\Http\Requests\UserData\GetProjectRequest;
use App\Http\Requests\UserData\GetSurepvisorRequest;
use App\Http\Requests\UserData\SetCounterpartyRequest;
use App\Http\Requests\UserData\SetPlaceRequest as SetPlaceModerationRequest;
use App\Http\Requests\UserData\SetProjectRequest;
use App\Http\Requests\UserData\SetSurepvisorsRequest;
use App\Http\Requests\UserData\SetUserImgRequest;
use App\Http\Resources\CounterpartyResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\OneOrderResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ShortUserResource;
use App\Http\Resources\UserForModerationResource;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Fields;
use App\Models\Order\Order;
use App\Models\User\Role;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Nopaper\NopaperService;
use App\Services\Register\EmailService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Resources\UserResource;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\ConfirmUserRequest;
use App\Services\Local\Repositories\Contracts\UserRepository;
use App\Http\Resources\SuccessResource;
use App\Http\Requests\PaginatorRequest;
use App\Enum\User\UserStatusModerationEnum;

class AdminController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function getCounterparty(){
        return CounterpartyResource::collection(Counterparty::get());
    }

    public function setCounterparty(SetCounterpartyRequest $request)
    {
        $user = User::where('id',$request->userId)->first();
        $user->counterparty()->syncWithoutDetaching($request->counterpartyIds);
        return new SuccessResource();
    }

    public function deleteCounterparty(DeleteCounterpartyRequest $request)
    {
        $user = User::where('id', $request->userId)->first();
        $user->counterparty()->detach($request->counterpartyId);
        $user = User::where('id', $request->userId)->first();
        $projectsForCounterparty = $user->counterparty
            ->flatMap(fn($counterparty) => $counterparty->projects)
            ->unique('id')->pluck('id')->toArray();

        $projectUser = $user->project?->pluck('id')->toArray();
        $result      = array_diff($projectUser, $projectsForCounterparty);
        if ($result) {
            $user->project()->detach($result);
        }

        $user          = User::where('id', $request->userId)->first();
        $placesProject = $user->project
            ->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->places)
            ->unique('id')?->pluck('id')->toArray();
        $places        = $user->place?->pluck('id')->toArray();
        $result        = array_diff($places, $placesProject);
        if ($result) {
            $user->place()->detach($result);
        }

        return new SuccessResource();
    }

    public function getProject(GetProjectRequest $request){
        $user = User::where('id',$request->userId)->first();
        $projects = $user->counterparty
            ->flatMap(fn($counterparty) => $counterparty->projects)
            ->where('date_end','>', Carbon::now())
            ->unique('id');
        return ProjectResource::collection($projects);
    }

    public function setProject(SetProjectRequest $request){
        $user = User::where('id',$request->userId)->first();
        $user->project()->syncWithoutDetaching($request->projectId);
        return new SuccessResource();
    }

    public function setUserImg(SetUserImgRequest $request){
        $user = User::where('id',$request->userId)->first();
        $project = Project::where('id',$request->projectId)->first();
        $projectLogo = $project?->brands()?->first()?->logo;
        $user->img = $projectLogo;
        $user->save();
        return new SuccessResource();
    }

    public function delProject(DelProjectRequest $request)
    {
        $user = User::where('id', $request->userId)->first();
        $user->project()->detach($request->projectId);
        $user          = User::where('id', $request->userId)->first();
        $placesProject = $user->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id')?->pluck('id')->toArray();
        $places        = $user->place?->pluck('id')->toArray();
        $result        = array_diff($places, $placesProject);
        if ($result) {
            $user->place()->detach($result);
        }
        return new UserResource($user->fresh());
    }

    public function getPlaceModeration(GetPlaceRequest $request)
    {
        $user = User::where('id',$request->userId)->first();
        $places = $user->project->where('date_end','>=', Carbon::now())
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
    }

    public function setPlaceModeration(SetPlaceModerationRequest $request): SuccessResource
    {
        $user          = User::where('id', $request->userId)->first();
        $placeForUser  = [];
        $placesProject = $user->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id')?->pluck('id')->toArray();
        foreach ($request->placeId as $place) {
            if (in_array($place, $placesProject)) {
                $placeForUser[] = $place;
            }
        }
        if ($placeForUser) {
            $user->place()->syncWithoutDetaching($placeForUser);
        }
        return new SuccessResource();
    }

    public function getUserSurepvisorData(GetSurepvisorRequest $request){
        $user = User::where('id',$request->userId)->first();
        $supervisorUsers = $user->supervisors;
        return ShortUserResource::collection($supervisorUsers);
    }

    public function getSurepvisors(GetSurepvisorRequest $request)
    {
        $user  = User::where('id', $request->userId)->first();
        $deleteSuperVisor = $user->supervisors?->pluck('id')->toArray();
        $users            = User::whereHas('roles', function ($query) {
            $query->where('role_id', RoleEnum::supervisor->value);
        });
        if ($deleteSuperVisor) {
            $users = $users->whereNotIn('id', $deleteSuperVisor);
        }
        $users = $users->where('confirmRegister', true)->where('finishRegister', true)->get();

        return ShortUserResource::collection($users);
    }

    public function setSurepvisors(SetSurepvisorsRequest $request){
        $user = User::where('id',$request->userId)->first();
        $user->supervisors()->syncWithoutDetaching($request->surepvisorIds);
        return new SuccessResource();
    }

    public function delSurepvisor(DelSurepvisorRequest $request){
        $user = User::where('id',$request->userId)->first();
        $user->supervisors()->detach($request->surepvisorId);
        return new SuccessResource();
    }

    public function getManager(GetManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $deleteManager = $user->manager?->pluck('id')->toArray();
        $users = User::whereHas('roles', function ($query) {
            $query->where('role_id', RoleEnum::manager->value);
        });
        if($deleteManager) {
            $users = $users->whereNotIn('id', $deleteManager);
        }
        $users = $users->where('confirmRegister', true)->where('finishRegister', true)->get();
        return ShortUserResource::collection($users);
    }

    public function setManagers(SetManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $user->manager()->syncWithoutDetaching($request->managerIds);
        return new SuccessResource();
    }

    public function delManager(DelManagerRequest $request){
        $user = User::where('id',$request->userId)->first();
        $user->manager()->detach($request->managerId);
        return new SuccessResource();
    }

    public function delPlaceModeration(DelPlaceModerationRequest $request): SuccessResource
    {
        $user = User::where('id',$request->userId)->first();
        $user->place()->detach($request->placeId);
        return new SuccessResource();
    }

    public function getDataProject(){
        $userProject = Auth::user()->project;
        return new ProjectResource($userProject);
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
            true
        );

        return UserForModerationResource::collection($usersForModeration);
    }

    public function getModerationSingleClient(GetClientRequest $request): UserForModerationResource
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
            $arrRoleConfirm,
            true
        );

        return new UserForModerationResource($usersForModeration);
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
            ->getModerationUsers($request->userId,$arrRoleConfirm,true);
        if(!empty($userForModeration)){
            $checkReg = false;
            if(!$userForModeration->confirmRegister){
                $checkReg = true;
            }

            if(isset($request->confirm)) {
                if ($request->confirm) {
                    if (true) {
                        $userForModeration->confirmRegister = true;
                        $userForModeration->finishRegister = true;
                    }
                } else {
                    $userForModeration->finishRegister = false;
                }
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
            (new NopaperService())->checkUserExists($userForModeration);
            if(!$userForModeration->confirmRegister && $checkReg) {
                EmailService::sendConfirmUserModeration($userForModeration);
            }
            $userForModeration->save();
        }

        return new SuccessResource();
    }

    public function getOrder(GetOrderRequest $request): OrderResource
    {
        return new OneOrderResource(
            Order::where('id',$request->orderId)->first()
        );
    }

}
