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

    /**
     * @OA\Post(
     *     path="/api/sendPhone/",
     *     operationId="sendPhone",
     *     tags={"register/auth"},
     *     summary="sendPhone",
     *     description="sendPhone Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"phone"},
     *                 @OA\Property(property="phone",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="success",
     *       @OA\JsonContent()
     *     ),
     * )
     */

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
                   if($user->finishRegister){
                       ///?????
                   }else {
                       $response['result']['type'] = 'register';
                   }
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

    /**
     * @OA\Post(
     *     path="/api/checkCode/",
     *     operationId="checkCode",
     *     tags={"register/auth"},
     *     summary="checkCode",
     *     description="checkCode Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"phone","code"},
     *                 @OA\Property(property="phone",type="number"),
     *                 @OA\Property(property="code",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="success",
     *       @OA\JsonContent()
     *     ),
     * )
     */

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
            if(!$user->confirmRegister) {
                if(!$user->finishRegister) {
                    $token = $user->createToken('UserToken', ['register'])->accessToken;
                }else{
                    ///???????????
                }
            }else{
                $token = $user->createToken('UserToken', ['personalArea'])->accessToken;
            }
            //авторизация


            $response['result']['token'] = $token;
            $response['status'] = 'success';
        }else{
            $response['result']['code'] = $smsCodeResult;
            $response['status'] = 'error';
        }
        return response()->json($response,200);
    }


    /**
     * @OA\Post(
     *     path="/api/setUserPin/",
     *     operationId="setUserPin",
     *     tags={"register/auth"},
     *     summary="setUserPin",
     *     description="setUserPin Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"pin"},
     *                 @OA\Property(property="pin",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="success",
     *       @OA\JsonContent()
     *     ),
     * )
     */


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
