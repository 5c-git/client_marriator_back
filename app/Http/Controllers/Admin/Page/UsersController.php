<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\FormBuilderService;


class UsersController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }


    public function usersList(Request $request)
    {
        $users = User::get();
        return view('admin.user.users',compact('users'));
    }

    public function userEdit(Request $request)
    {
        $user = User::where('id','=',$request->id)->first();
        if(!empty($user->data)){
            $user->data = json_decode($user->data,true);
            if(!empty($user->data[1])){
                $user->data = json_encode(array_merge(...$user->data));
                $user->save();
                $user->data = json_decode($user->data,true);
            }
        }else{
            $user->data = [];
        }
        if(!empty($user->expansionData)){
            $user->expansionData = json_decode($user->expansionData,true);
        }else{
            $user->expansionData = [];
        }
        if(!empty($user->errorData)){
            $user->errorData = json_decode($user->errorData,true);
        }else{
            $user->errorData = [];
        }


        $fields = (new FormBuilderService(10, $user->data))->getUserField($user->expansionData,$user->errorData);


        return view('admin.user.userEdit',compact('user','fields'));
    }

    public function usersCreate(Request $request)
    {
        return view('admin.user.create');
    }

    public function userDelete(Request $request)
    {
        if(Auth::user()->id != $request->id) {
            User::where('id', '=', $request->id)->delete();
        }
        return back();
    }

    public function userEditAjax(Request $request)
    {
        $data = $request->all();

        $errorData = [];
        if(!empty($data['error'])){
            foreach ($data['error'] as $uuidError=>$error){
                if(!empty($error)){
                    $errorData[$uuidError] = $error;
                }
            }
        }

        $expansionData = [];
        if(!empty($data['moreData'])){
            foreach ($data['moreData'] as $uuidMoreData=>$moreData){
                $expansionDataOne = [];
                if(!empty($moreData)){
                    foreach ($moreData["name"] as $k=>$moreDataName){
                        if(!empty($moreDataName) && !empty($moreData["value"][$k])){
                            $expansionDataOne[] = ['name'=>$moreDataName,'value'=>$moreData["value"][$k]];
                        }
                    }
                    if(!empty($expansionDataOne)) {
                        $expansionData[$uuidMoreData] = $expansionDataOne;
                    }
                }
            }
        }



        $user = User::where('id','=',$data["id"])->first();
        if($data["password"] == $data["confirmPassword"] && !empty($data["password"])) {
            $user->password = Hash::make($data['password']);
        }
        $user->expansionData = json_encode($expansionData);
        $user->errorData = json_encode($errorData);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];


        if(!empty($data['permission'])) {
            $this->userRoleChange($data['permission'], $data["id"]);
        }else{
            $this->userRoleChange('off', $data["id"]);
        }

        $user->save();
        $response['status'] = 'success';

        return response()->json($response);
    }

    public function usersCreateAjax(Request $request)
    {
        $data = $request->all();
        if($data["password"] == $data["confirmPassword"] && !empty($data["password"])) {
            $user['password'] = Hash::make($data['password']);
        }else{
            $user['password'] = Hash::make(rand(1000000,999999));
        }


        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        $user->phone = $data['phone'];

        //$user['email_verified_at'] = Carbon::now();

        $user = User::create($user);

        if($user->id){
            if(!empty($data['permission'])) {
                $this->userRoleChange($data['permission'], $user->id);
            }else{
                $this->userRoleChange('off', $user->id);
            }
        }
        $response['status'] = 'success';
        $response['url'] = '/admin/users/edit/'.$user->id;

        return response()->json($response);
    }


    public function userRoleChange($status = '', $userId){
        if($status == 'on'){
            $status = 1;
        }else{
            $status = 2;
        }
        $userRole = UserRole::where('user_id','=',$userId)->first();
        if($userRole){
            $userRole->role_id = $status;
            $userRole->save();
        }else{
            $userRole = new UserRole;
            $userRole->user_id = $userId;
            $userRole->role_id = $status;
        }
        $userRole->save();

    }

}
