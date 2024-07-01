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

class UserPersonalInfoController extends Controller
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
     *     path="/api/getUserInfo/",
     *     operationId="getUserInfo",
     *     tags={"Personal area"},
     *     summary="getUserInfo",
     *     description="getUserInfo Endpoint",
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

    /**
     * @OA\Get(
     *     path="/api/getUserInfo/",
     *     operationId="getUserInfo",
     *     tags={"Personal area"},
     *     summary="getForm",
     *     description="getUserInfo Endpoint",
     *     @OA\Response(
     *       response="200",
     *       description="uset info",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"userData":{},}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getUserInfo(Request $request){
        $user = Auth::user();
        $response['result']['userData']=$user->toArray();
        $response['status'] = 'success';
        return response()->json($response,200);
    }
}
