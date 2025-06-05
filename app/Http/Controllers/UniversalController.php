<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmUserRequest;
use App\Http\Requests\DelPlaceRequest;
use App\Http\Requests\Order\AcceptOrderRequest;
use App\Http\Requests\Order\GetOrderRequest;
use App\Http\Requests\Order\GetTaskRequest;
use App\Http\Requests\PaginatorRequest;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Requests\SetPlaceRequest;
use App\Http\Requests\SetUserDataRequest;
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
}
