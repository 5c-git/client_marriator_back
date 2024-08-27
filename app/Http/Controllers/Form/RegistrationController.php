<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\Register\EmailVerifiedService;
use Carbon\Carbon;
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
     *     summary="Отправить номер телефона для регистрации или авторизации",
     *     description="Метод отправления номера телефона для регистрации или авторизации",
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
     *       description="Номер телефона успешно отправлен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"type":"auth|register","code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Неуспешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "error", "error":"Поле телефон обязательна для заполнения"},summary="Нехватка полей"),
     *           @OA\Examples(example="error result", value={"status": "error","result":{"type":"auth|register","code":{"status":"errorSend"}}},summary="Успешный запрос"),
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
                       $response['result']['type'] = 'register';
                   }else {
                       $response['result']['type'] = 'register';
                   }
               }
           }else{
               $user = new User();
               $user->phone = $request->phone;
               $user->email = Str::random(20).'@mariator.ru';
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
     *     summary="Отправить код подтверждения номера телефона",
     *     description="Метод отправки кода подтверждения номера телефона",
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
     *       description="Код подтверждения отправлен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"},}},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка в номере телефона или в коде подтверждения",
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

        $smsCodeResult = (new SmsCodeService($request->phone,(int)$request->code))->checkCode();
        if($smsCodeResult['status'] == 'success'){
            $user = User::where('phone',$request->phone)->first();
            $apiTokenService = new ApiTokenService($user);
            if(!$user->confirmRegister) {
                if(!$user->finishRegister) {
                    $token = $apiTokenService->createToken(['register']);
                    $response['result']['token'] = $token;
                }else{
                    ///???????????
                }
            }else{
                $token = $apiTokenService->createToken(['checkPin']);
                $response['result']['token'] = $token;
            }
            //авторизация


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
     *     summary="Установить пин-код",
     *     description="Метод установки пин-кода",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"pin"},
     *                 @OA\Property(property="pin",type="number", description="pin - 4 цифры"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Пин-код успешно установлен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка или отсутствие пин-кода",
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

    /**
     * @OA\Post(
     *     path="/api/startRestorePin/",
     *     operationId="startRestorePin",
     *     tags={"Personal area"},
     *     summary="Восстановить пин-код",
     *     description="Метод восстановения пин-кода",
     *     @OA\Response(
     *       response="200",
     *       description="Успешно получены код подтверждения номера телефона и токен доступа",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"},"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"}}},summary="Успешный запрос"),
     *       )
     *     ),
     * )
     */

    public function startRestorePin(Request $request){
        $user = Auth::user();
        $smsCodeService = new SmsCodeService($user->phone);
        $response['result']['code'] = $smsCodeService->createCode();
        $response['status'] = $smsCodeService->status;

        if($response['status'] == 'success') {
            $apiTokenService = new ApiTokenService($user);
            $token = $apiTokenService->createToken(['restorePin']);
            $response['result']['token'] = $token;
        }
        return response()->json($response,200);
    }


    /**
     * @OA\Post(
     *     path="/api/checkCodeRestore/",
     *     operationId="checkCodeRestore",
     *     tags={"Personal area"},
     *     summary="Отправить код подтверждения при восстановлении пин-кода",
     *     description="Метод отправки смс-код при восстановлении пин-кода",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"code"},
     *                 @OA\Property(property="code",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="sms-код успешно отправлен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"},}},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Номер телефона или код не указан",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result code", value={"status": "error", "error":"Поле код обязательна для заполнения"},summary="Ошибка кода"),
     *       )
     *     ),
     * )
     */

    public function checkCodeRestore(Request $request){
        if(empty($request->code)){
            $response['error'] = 'Поле код обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }
        $user = Auth::user();

        $smsCodeResult = (new SmsCodeService($user->phone,(int)$request->code))->checkCode();
        if($smsCodeResult['status'] == 'success'){
            $user->pin = null;
            $user->save();
            $apiTokenService = new ApiTokenService($user);
            if(!$user->confirmRegister) {
                if(!$user->finishRegister) {
                    $token = $apiTokenService->createToken(['register']);
                    $response['result']['token'] = $token;
                }else{
                    ///???????????
                }
            }else{
                $token = $apiTokenService->createToken(['checkPin']);
                $response['result']['token'] = $token;
            }


            $response['status'] = 'success';
        }else{
            $response['result']['code'] = $smsCodeResult;
            $response['status'] = 'error';
        }
        return response()->json($response,200);
    }


    /**
     * @OA\Post(
     *     path="/api/setUserEmail/",
     *     operationId="setUserEmail_reg",
     *     tags={"register/auth"},
     *     summary="Поменять email пользователя",
     *     description="Метод изменения email пользователя",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email"},
     *                 @OA\Property(property="email",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Успешно получен код для подтверждения email",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Неуспешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "error", "error":"Email отсутствует"},summary="Нехватка полей"),
     *       )
     *     ),
     * )
     */

    public function setUserEmail(Request $request)
    {
        $user = Auth::user();
        if (!empty($request->email) && (User::where('email',$request->email)->doesntExist() || $user->email == $request->email)) {
            $user->email = $request->email;
            $emailCodeService = new EmailVerifiedService($request->email);
            $response['result']['code'] = $emailCodeService->createCode();
            $response['status'] = $emailCodeService->status;
            if ($emailCodeService->status == 'success') {
                $user->email_verified_at = null;
                $user->save();
            }
            return response()->json($response, 200);
        } else {
            $response['error'] = 'Email отсутствует или присвоен другому пользователю';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        return response()->json($response);
    }


    /**
     * @OA\Post(
     *     path="/api/checkEmailCode/",
     *     operationId="checkEmailCode_reg",
     *     tags={"register/auth"},
     *     summary="Подтвердить изменение email",
     *     description="Метод подтверждения смены email",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"code"},
     *                 @OA\Property(property="code",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Код подтверждения изменения email отправлен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Код не указан",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result code", value={"status": "error", "error":"Поле код обязательна для заполнения"},summary="Ошибка кода"),
     *       )
     *     ),
     * )
     */

    public function checkEmailCode(Request $request)
    {
        if (empty($request->code)) {
            $response['error'] = 'Поле код обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        $user = Auth::user();
        $emailCodeResult = (new EmailVerifiedService($user->email, (int)$request->code))->checkCode();
        if ($emailCodeResult['status'] == 'success') {
            $user->email_verified_at = Carbon::now();
            $user->save();
            $response['status'] = 'success';
        } else {
            $response['result']['code'] = $emailCodeResult;
            $response['status'] = 'error';
        }
        return response()->json($response, 200);
    }






}
