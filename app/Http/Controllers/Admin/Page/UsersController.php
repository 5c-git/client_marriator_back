<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        return view('admin.user.userEdit',compact('user'));
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
        $user = User::where('id','=',$data["id"])->first();
        if($data["password"] == $data["confirmPassword"] && !empty($data["password"])) {
            $user->password = Hash::make($data['password']);
        }


        $user->name = $data['name'];
        $user->email = $data['email'];


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
        $user['email_verified_at'] = Carbon::now();

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
