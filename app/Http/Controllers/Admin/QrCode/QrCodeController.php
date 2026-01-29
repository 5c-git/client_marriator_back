<?php

namespace App\Http\Controllers\Admin\QrCode;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User\Role;
use App\Enum\Role\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class QrCodeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index(){

        $users = User::query()->whereNotNull('register_hash')->get();

        $roles = Role::query()->whereNotIn('name',['admin','specialist','supervisor','recruiter'])->get();
        return view('admin.qrCode.qrCode',compact('roles','users'));
    }

    public function getBindings(Request $request)
    {
        $data = [];
        $name = [];
        $userFields = [];
        foreach ($request->roles as $role) {
            $data[(RoleEnum::from($role)?->getUserBinding())::$nameCustom] = (RoleEnum::from($role)?->getUserBinding())::where('date_start', '<=', Carbon::now())
                ->where('date_end', '>=', Carbon::now())->get()->toArray();
            $name[(RoleEnum::from($role)?->getUserBinding())::$nameCustom] = (RoleEnum::from($role)?->getUserBinding())::$nameCustom;
            $userFields[(RoleEnum::from($role)?->getUserBinding())::$nameCustom] = RoleEnum::from($role)?->getUserBindingFunction();
        }
        $i = 0;
        foreach ($data as $k=>$dataBindings){
            $response['data'][] = $dataBindings;
            $response['name'][$i] = $name[$k];
            $response['userFields'][$i] = $userFields[$k];
            $i++;
        }
        if(!empty($response['data'])){
            $response['status'] = 'success';
        }else{
            $response = ['status'=>'error'];
        }

        return response()->json($response);
    }

    public function createUserLink(Request $request)
    {
        if(empty($request->phone) || empty($request->email)){
            return response()->json([]);
        }
        if(!User::query()->where('phone',$request->phone)->where('email',$request->email)->exists()) {
            $user = new User();
            $user->phone = $request->phone;
            $user->password = Hash::make(rand(1000000,9999999));
            $user->email = $request->email;
            $user->register_hash = str_replace('/', "", Hash::make(date('Y-m-d H:i:s').$request->phone.rand(1000000,9999999)));
            $user->save();
            $userFunction = [];
            foreach ($request->roles as $role){
               $userFunction[] = RoleEnum::from($role)?->getUserBindingFunction();
            }
            if(!empty($userFunction)){
                $userFunction = array_unique($userFunction);
                foreach ($userFunction as $function){
                    if(!empty($request->$function)){
                        $user->$function()->sync($request->$function);
                    }
                }
            }

            $brand = $user->project()?->first()?->brands()?->first();
            $logoBrand = $brand?->logo;
            $place = $user->place()?->first()?->project()?->first()?->brands()?->first();
            $logoPlace = $place?->logo;
            $user->img = $logoBrand?:$logoPlace;

            $user->roles()->sync($request->roles);

            $user = User::find($user->id);
            $projects = $user->project()->with('counterparties')->get();
            $counterpartyIds = collect();
            foreach ($projects as $project) {
                $counterpartyIds = $counterpartyIds->merge(
                    $project->counterparties->pluck('id')
                );
            }
            $counterpartyIds = $counterpartyIds->unique();
            $user->counterparty()->syncWithoutDetaching($counterpartyIds->toArray());

            $response = ['status'=>'success','data'=>['url'=> config('app.front').'/signin/client/phone?hash='.$user->register_hash]];
        }else{
            $response = ['status'=>'error','error_message'=>'Пользователь с такими данными уже существует'];
        }
        return response()->json($response);
    }


}
