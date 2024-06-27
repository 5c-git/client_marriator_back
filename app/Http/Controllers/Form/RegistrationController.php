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
     *       description="send phone start register or auth",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"type":"auth|register","code":{"status":"exists|success|errorSend","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="phone is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "error", "error":"Поле телефон обязательна для заполнения"},summary="Нехватка полей"),
     *       )
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
     *       description="check sms code success",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"token":"token",}},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="phone or code is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result code", value={"status": "error", "error":"Поле код обязательна для заполнения"},summary="Ошибка кода"),
     *           @OA\Examples(example="result phone", value={"status": "error", "error":"Поле телефон обязательна для заполнения"},summary="Ошибка телефона"),
     *       )
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
                //$token = $user->createToken('UserToken', ['personalArea'])->accessToken;
                $token = $user->createToken('UserToken', ['checkPin'])->accessToken;
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
     *       description="set pin success",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="pin is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result pin", value={"status": "error", "error":"Поле пин код обязательна для заполнения"},summary="Нехватка полей"),
     *       )
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
