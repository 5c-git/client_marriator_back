<?php

namespace App\Http\Controllers\UserRoles;

use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\Order\AcceptAllReportRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\AcceptReportRequest;
use App\Http\Requests\Order\AcceptSpecialistRequest;
use App\Http\Requests\Order\AcceptTaskRequest;
use App\Http\Requests\Order\AddReasonsRequest;
use App\Http\Requests\Order\CreateBidFromOrderRequest;
use App\Http\Requests\Order\CreateBidFromTaskRequest;
use App\Http\Requests\Order\CreateSearchFromOrderRequest;
use App\Http\Requests\Order\CreateSearchFromTaskRequest;
use App\Http\Requests\Order\DeclinedSpecialistRequest;
use App\Http\Requests\Order\EndSpecialistJobRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Requests\Order\GetJobRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\PayReportRequest;
use App\Http\Requests\Order\SearchDataRequest;
use App\Http\Requests\Order\UpdateReportRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Resources\AcceptingUsersResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Order\BidResource;
use App\Http\Resources\Order\JobResource;
use App\Http\Resources\Order\OneOrderResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\ReportResource;
use App\Http\Resources\Order\SearchResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RadiusResponse;
use App\Http\Resources\ReasonsResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserForModerationResource;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Radius;
use App\Models\Fields\Directory\Reasons;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\Order\Task;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\OrderRepository;
use App\Services\Local\Repositories\Contracts\UserRepository;
use App\Services\Nopaper\NopaperService;
use App\Services\PVP\PVPService;
use App\Services\Register\EmailService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Http\Resources\Order\TaskResource;
use App\Http\Requests\Order\GetTaskRequest;
use App\Enum\User\SortEnum;
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
use App\Http\Requests\Order\CreateRequestFromTaskRequest;
use App\Http\Requests\Order\CreateRequestFromBidRequest;
use App\Http\Requests\Order\CancelRequestRequest;
use App\Http\Resources\Order\RequestResource;
use App\Http\Requests\UserData\GetClientRequest;
use App\Models\Fields\Directory\Counterparty;
use App\Http\Resources\CounterpartyResource;
use App\Http\Requests\UserData\SetCounterpartyRequest;
use App\Http\Requests\UserData\DeleteCounterpartyRequest;
use App\Http\Resources\Order\BidShortResource;

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

    public function getCounterparty(){
        /** @var  $user User */
        $user = Auth::user();
        return CounterpartyResource::collection($user->counterparty);
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
            $user = Auth::user();
            $projectsAdmin = $user->counterparty
                ->flatMap(fn($counterparty) => $counterparty->projects)
                ->unique('id');

            $commonProjects = $projects->intersect($projectsAdmin);
            return ProjectResource::collection($commonProjects);
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

    public function getPlaceModeration(GetPlaceRequest $request)
    {
        $user = User::where('id',$request->userId)->first();
        $userRoles = $user->roles?->pluck('id')->toArray();
        if(in_array($userRoles[0],[RoleEnum::manager->value,RoleEnum::client->value,RoleEnum::specialist->value])){
            $places = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id');
            $user = Auth::user();
            $placesAdmin = $user->project
                ->flatMap(fn($project) => $project->places)
                ->unique('id');

            $commonPlace = $places->intersect($placesAdmin);
            return PlaceResource::collection($commonPlace);
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
            $arrRoleConfirm
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
            ->getModerationUsers($request->userId,$arrRoleConfirm);
        if(!empty($userForModeration)){
            $checkReg = false;
            if(!$userForModeration->confirmRegister){
                $checkReg = true;
            }

            if(isset($request->confirm)) {
                if ($request->confirm) {
                    if (true) {
                        $userForModeration->confirmRegister = true;
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


    public function getPlace()
    {
        $places = Auth::user()->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
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
        return new OneOrderResource(
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

    public function createSearchFromOrder(CreateSearchFromOrderRequest $request){
        $user = $request->user();
        return new SearchResource($this->orderRepository->createSearchFromOrder(
            $user,
            $request->orderId,
            $request->orderActivityId
        ));
    }

    public function createSearchFromTask(CreateSearchFromTaskRequest $request){
        $user = $request->user();
        return new SearchResource($this->orderRepository->createSearchFromTask(
            $user,
            $request->taskId,
            $request->taskActivityId
        ));
    }

    public function updateSearch(SearchDataRequest $request){
        return new SearchResource($this->orderRepository->updateSearch($request));
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
        return AcceptingUsersResource::collection($this->orderRepository->getSpecialistForBid($request->bidId));
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
        if($bid->external_id) {
            $userSpec = User::where('id',$request->specialistId)->first();
            $pvpService = PVPService::getObj($bid->external_type);
            if($pvpService->assignToShift($userSpec,$bid->external_id) === null){
                return new ErrorResource();
            }
        }
        $bid->acceptingUsers()->updateExistingPivot($request->specialistId, [
            'accepted' => BidAcceptingStatusEnum::work->value,
        ]);
        $count = $bid->acceptingUsers()->where('accepted',BidAcceptingStatusEnum::work->value)->count();
        $countAll = $bid->acceptingUsers()->whereIn('accepted',[
            BidAcceptingStatusEnum::work->value,
            BidAcceptingStatusEnum::accepted->value,
            BidAcceptingStatusEnum::notAccepted->value,
            BidAcceptingStatusEnum::consideration->value,
        ])->count();
        if ($count >= $bid->count || $countAll < $bid->count) {
            $bid->status = OrderStatusEnum::accepted->value;
            $bid->save();
        }
        return new SuccessResource();
    }

    public function declinedSpecialist(DeclinedSpecialistRequest $request)
    {
        $bid = Bid::where('id',$request->bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot($request->specialistId, [
            'accepted' => BidAcceptingStatusEnum::declined->value,
        ]);
        return new SuccessResource();
    }

    public function getJobs(){
        $user = Auth::user();
        $bids = $this->orderRepository->getJobsByUserSyncDataPaginate(
            $user
        );
        $expandedBids = $bids->flatMap(function ($bid) {
            return $bid->acceptingUsers->map(function ($acceptingUser) use ($bid) {
                $newBid = clone $bid;
                $newBid->acceptingUser = $acceptingUser;
                return $newBid;
            });
        });
        return JobResource::collection($expandedBids);
    }

    public function getJob(GetJobRequest $request){
        return new JobResource($this->orderRepository->getJobByUser($request));
    }

    public function endJob(EndSpecialistJobRequest $request){
        $bid = Bid::where('id',$request->bidId)->first();
        $bid->acceptingUsers()->updateExistingPivot($request->specialistId, [
            'accepted' => BidAcceptingStatusEnum::canceled->value,
        ]);
        return new SuccessResource();
    }

    public function acceptReport(AcceptReportRequest $request){
        /** @var  $report Report */
        $report = Report::where('id',$request->reportId)->first();
        $report->status = ReportStatusEnum::accept->value;
        $report->hours = $request->hours;
        $report->forPay = $this->getPriceForHour($report);
        $report->save();

        $report->reasons()->detach();
        $syncData = [];
        if(!empty($request->reasons)) {
            foreach ($request->reasons as $reason) {
                $syncData[] = $reason['reasonId'];
            }
        }

        if($syncData){
            $report->reasons()->syncWithoutDetaching($syncData);
        }
        return new SuccessResource();
    }

    public function acceptAllReportJob(AcceptAllReportRequest $request){
        $reports = Report::where('bid_id',$request->bidId)
            ->whereIn('status',[
                ReportStatusEnum::reported->value,
                ReportStatusEnum::notEnded->value,
                ReportStatusEnum::end->value,
            ])
            ->where('user_id',$request->specialistId)->get();
        $dataRequest = [];
        if(!empty($request->reports)) {
            foreach ($request->reports as $reportRequest) {
                $dataRequest[$reportRequest['reportId']] = [
                    'reasons' => $reportRequest['reasons'],
                    'hours'   => $reportRequest['hours']
                ];
            }
        }
        foreach ($reports as $report){
            /** @var  $report Report */
            $report->status = ReportStatusEnum::accept->value;
            $report->forPay = $this->getPriceForHour($report);
            $report->save();


            if(!empty($dataRequest[$report->id])){
                $report->hours = $dataRequest[$report->id]['hours'];
                $report->forPay = $this->getPriceForHour($report);
                $report->save();
                $report->reasons()->detach();
                $syncData = [];
                foreach ($dataRequest[$report->id]['reasons'] as $reason) {
                    $syncData[] = $reason['reasonId'];
                }
                if($syncData){
                    $report->reasons()->syncWithoutDetaching($syncData);
                }
            }
        }
        return new SuccessResource();
    }

    public function updateReport(UpdateReportRequest $request)
    {
        $report = Report::where('id',$request->reportId)->first();
        $report->hours = $request->hours;
        $report->forPay = $this->getPriceForHour($report);
        $report->save();
        $report->reasons()->detach();
        $syncData = [];
        if(!empty($request->reasons)) {
            foreach ($request->reasons as $reason) {
                $syncData[] = $reason['reasonId'];
            }
        }

        if($syncData){
            $report->reasons()->syncWithoutDetaching($syncData);
        }
        return new ReportResource($report);
    }

    public function payReport(PayReportRequest $request){
        /** @var  $report Report */
        $report = Report::where('id',$request->reportId)->first();
        $report->status = ReportStatusEnum::forPay->value;
        $report->save();
        return new SuccessResource();
    }

    private function getPriceForHour(Report $report): float
    {
        if ($report->order) {
            $project = $report->order->user->project->first();
        } elseif ($report->task) {
            $project = $report->task->project;
        }
        if(!$report->bid->price) {
            $price = 0;
            foreach ($project->viewActivities as $viewActivity) {
                if ($viewActivity->id === $report->bid->view_activity_id) {
                    $price = $viewActivity->pivot->price;
                    break;
                }
            }
            $price = $price * $report->hours;
        }else{
            $price = $report->bid->price * $report->hours;
        }
        return $price;
    }

    public function getReasons(){
        $reasons = Reasons::get();
        return ReasonsResource::collection($reasons);
    }

    public function addReasons(AddReasonsRequest $request): SuccessResource
    {
        $report = Report::where('id',$request->reportId)->first();
        $report->reasons()->attach($request->reasonId, [
            'amount' => $request->amount
        ]);
        return new SuccessResource();
    }

}
