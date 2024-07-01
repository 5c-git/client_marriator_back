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

    /**
     * @OA\Post(
     *     path="/api/checkPin/",
     *     operationId="checkPin",
     *     tags={"Personal area"},
     *     summary="checkPin",
     *     description="checkPin Endpoint",
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
     *       description="check pin code",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"},}},summary="Успех"),
     *           @OA\Examples(example="result check pin error", value={"status": "error"},summary="Неверный пин"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="pin is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result pin", value={"status": "error", "error":"Поле пин обязательна для заполнения"},summary="Ошибка кода"),
     *       )
     *     ),
     * )
     */

    public function checkPin(Request $request){
        if(empty($request->pin)){
            $response['error'] = 'Поле пин обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }else{
            $user = Auth::user();
            $user->pin = 1234;
            $user->save();
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

    /**
     * @OA\Post(
     *     path="/api/refreshToken/",
     *     operationId="refreshToken",
     *     tags={"Personal area"},
     *     summary="refreshToken",
     *     description="refreshToken Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"refreshToken"},
     *                 @OA\Property(property="refreshToken",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="refresh token success",
     *       @OA\JsonContent(
     *           @OA\Examples(example="success refresh token", value={"status": "success","result":{"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"},}},summary="Успешное востановление"),
     *           @OA\Examples(example="error refresh token", value={"status": "success","result":{"token": {"error":"invalid_request","error_description":"The refresh token is invalid.","hint":"Token is not linked to client","message":"The refresh token is invalid."},}},summary="Неуспешное востановление"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="refreshToken is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result refreshToken", value={"status": "error", "error":"Поле refreshToken обязательна для заполнения"},summary="Ошибка токена"),
     *       )
     *     ),
     * )
     */

    public function refreshToken(Request $request){
        if(empty($request->refreshToken)){
            $response['error'] = 'Поле refreshToken обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response,417);
        }else {
            $response['result']['token'] = ApiTokenService::refreshToken($request->refreshToken);
            $response['status'] = 'success';
        }
        return response()->json($response,200);
    }

}
