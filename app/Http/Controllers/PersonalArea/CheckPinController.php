<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;

class CheckPinController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function checkPin(Request $request){
        if(empty($request->pin)){
            $response['error'] = 'Поле пин обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }else{
            $user = Auth::user();
            if(!empty($user) && $user->pin == $request->pin){
                $token = $user->createToken('UserToken', ['personalArea'])->accessToken;
                $response['result']['token'] = $token;
                $response['status'] = 'success';
            }else{
                $response['status'] = 'error';
            }

            return response()->json($response,200);
        }
    }
    
}
