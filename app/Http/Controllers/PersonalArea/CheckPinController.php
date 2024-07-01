<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
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
                $apiTokenService = new ApiTokenService($user);
                $token = $apiTokenService->createToken(['personalArea']);
                $response['result']['token'] = $token;
                $response['status'] = 'success';
            }else{
                $response['status'] = 'error';
            }

            return response()->json($response,200);
        }
    }

    public function refreshToken(Request $request){
        if(empty($request->refreshToken)){
            $response['error'] = 'Поле refreshToken обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }else {
            $response['result']['token'] = json_decode(ApiTokenService::refreshToken($request->refreshToken));
            $response['status'] = 'success';
        }
        return response()->json($response,200);
    }

}
