<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;

class RegistrationController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function sendPhone(Request $request){
        if(empty($request->phone)){
            $response['error'] = 'Поле телефон обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }else{
           $user = User::where('phone',$request->phone)->first();
           if(!empty($user)){
               if($user->confirmRegister) {
                   $response['result']['type'] = 'auth';
               }else{
                   $response['result']['type'] = 'register';
               }
           }else{
               $user = new User();
               $user->phone = $request->phone;
               $user->email = Str::random(10).'@mariator.ru';
               $user->password = Hash::make(Str::random(20));
               $user->save();
               $response['result']['type'] = 'register';
           }

            $smsCodeService = new SmsCodeService($request->phone);
            $response['result']['code'] = $smsCodeService->createCode();
            $response['status'] = $smsCodeService->status;

            return response()->json($response,200);
        }
    }

    public function checkCode(Request $request){
        if(empty($request->code)){
            $response['error'] = 'Поле код обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }
        if(empty($request->phone)){
            $response['error'] = 'Поле телефон обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }

        $smsCodeResult = (new SmsCodeService($request->phone,$request->code))->checkCode();
        if($smsCodeResult['status'] == 'success'){
            $user = User::where('phone',$request->phone)->first();

            //авторизация



            $response['status'] = 'success';
        }else{
            $response['result']['code'] = $smsCodeResult;
            $response['status'] = 'error';
        }
        return response()->json($response,200);
    }


    public function setUserPin(Request $request){
        if(empty($request->pin)){
            $response['error'] = 'Поле пин код обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }
        $user = Auth::user();
        $user->pin = $request->pin;
        $user->save();
        $response['status'] = 'success';
        return response()->json($response,200);
    }


}
