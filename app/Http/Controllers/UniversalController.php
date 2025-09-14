<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\AcceptSpecialistRequest;
use App\Http\Requests\Order\AcceptTaskRequest;
use App\Http\Requests\Order\BidDataRequest;
use App\Http\Requests\Order\CancelBidRequest;
use App\Http\Requests\Order\CreateBidFromOrderRequest;
use App\Http\Requests\Order\CreateBidFromTaskRequest;
use App\Http\Requests\Order\EntrustBidRequest;
use App\Http\Requests\Order\GetBidRequest;
use App\Http\Requests\Order\GetBidsRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\GetPlaceForBidRequest;
use App\Http\Requests\Order\GetSpecialistForBisRequest;
use App\Http\Requests\Order\GetTaskRequest;
use App\Http\Requests\Order\GetViewActivitiesForOrderRequest;
use App\Http\Requests\Order\GetViewActivitiesForTaskRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Requests\SetUserDataRequest;
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
use FontLib\Table\Type\glyf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UniversalController extends Controller
{

    private array|null $roles;

    public function __construct()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user = Auth::user();
        $this->roles = $user->roles?->pluck('name')->toArray();
    }

    public function getBrand(Request $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->getBrand($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getBrand($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setBrandImg(SetBrandImgRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->setBrandImg($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setBrandImg($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getPlace(Request $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->getPlace($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getPlace($request);
        }
        if(in_array('recruiter',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\RecruiterController::class)->getPlace($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getPlace($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setPlace(SetPlaceRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->setPlace($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setPlace($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function delPlace(DelPlaceRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->delPlace($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->delPlace($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setUserData(SetUserDataRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->setUserData($request);
        }
        if(in_array('recruiter',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\RecruiterController::class)->setUserData($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setUserData($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getOrders(GetOrderRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->getOrders($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getOrders($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getOrders($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getOrders($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getOrder(GetOrderRequest $request){
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->getOrder($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getOrder($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getOrder($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getOrder($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getModerationClient(PaginatorRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getModerationClient($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getModerationClient($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getModerationClient($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getModerationSingleClient(GetClientRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getModerationSingleClient($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getModerationSingleClient($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getModerationSingleClient($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function confirmUserRegister(ConfirmUserRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->confirmUserRegister($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->confirmUserRegister($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->confirmUserRegister($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function acceptOrder(AcceptOrderRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->acceptOrder($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->acceptOrder($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getTasks(GetTaskRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getTasks($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getTasks($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getTask(GetTaskRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getTask($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getTask($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getProject(GetProjectRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getProject($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getProject($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getProject($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setProject(SetProjectRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->setProject($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setProject($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->setProject($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setUserImg(SetUserImgRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->setUserImg($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setUserImg($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->setUserImg($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getViewActivitiesForTask(GetViewActivitiesForTaskRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getViewActivitiesForTask($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getViewActivitiesForTask($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getPlaceForOrder(){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getPlace();
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getPlace();
        }
        if(in_array('client',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ClientController::class)->getPlace();
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function delProject(DelProjectRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->delProject($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->delProject($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->delProject($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getPlaceModeration(GetPlaceRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getPlaceModeration($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getPlaceModeration($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getPlaceModeration($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setPlaceModeration(SetPlaceModerationRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->setPlaceModeration($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setPlaceModeration($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->setPlaceModeration($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function delPlaceModeration(DelPlaceModerationRequest $request){
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->delPlaceModeration($request);
        }
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->delPlaceModeration($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->delPlaceModeration($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getUserSurepvisorData(GetSurepvisorRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getUserSurepvisorData($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getUserSurepvisorData($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getSurepvisors(GetSurepvisorRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getSurepvisors($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getSurepvisors($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setSurepvisors(SetSurepvisorsRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setSurepvisors($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->setSurepvisors($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function delSurepvisor(DelSurepvisorRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->delSurepvisor($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->delSurepvisor($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getCounterparty(){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getCounterparty();
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->getCounterparty();
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getCounterparty();
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function setCounterparty(SetCounterpartyRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->setCounterparty($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->setCounterparty($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->setCounterparty($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function deleteCounterparty(DeleteCounterpartyRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->deleteCounterparty($request);
        }
        if(in_array('admin',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\AdminController::class)->deleteCounterparty($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->deleteCounterparty($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function acceptTask(AcceptTaskRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->acceptTask($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->acceptTask($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }


    public function createBidFromOrder(CreateBidFromOrderRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->createBidFromOrder($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->createBidFromOrder($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function createBidFromTask(CreateBidFromTaskRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->createBidFromTask($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->createBidFromTask($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getBids(GetBidsRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getBids($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getBids($request);
        }
        if(in_array('specialist',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SpecialistController::class)->getBids($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getBid(GetBidRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getBid($request);
        }
        if(in_array('specialist',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SpecialistController::class)->getBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function invoiceBid(EntrustBidRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->invoiceBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->invoiceBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getPlaceForBid(){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getPlaceForBid();
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getPlaceForBid();
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getRadiusSelect(){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getRadiusSelect();
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getRadiusSelect();
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function updateBid(BidDataRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->updateBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->updateBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function getSpecialistForBid(GetSpecialistForBisRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->getSpecialistForBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->getSpecialistForBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function instructBid(EntrustBidRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->instructBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->instructBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function cancelBid(CancelBidRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->cancelBid($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->cancelBid($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function acceptSpecialist(AcceptSpecialistRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->acceptSpecialist($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->acceptSpecialist($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

    public function declinedSpecialist(AcceptSpecialistRequest $request){
        if(in_array('manager',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\ManagerController::class)->declinedSpecialist($request);
        }
        if(in_array('supervisor',$this->roles)){
            return app(\App\Http\Controllers\UserRoles\SupervisorController::class)->declinedSpecialist($request);
        }
        return response()->json(['message' => 'Role not allowed.'], 403);
    }

}
