<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\FormBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Enum\Fields\PersonalInfoSectionEnum;
use App\Services\Register\EmailVerifiedService;


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
     * @OA\Get(
     *     path="/api/personal/getUserInfo/",
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

    public function getUserInfo(Request $request)
    {
        $user = Auth::user();
        $user->img = config('app.url') . Storage::url($user->img);
        $response['result']['userData'] = $user->toArray();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getUserFields/",
     *     operationId="getUserFields",
     *     tags={"Personal area"},
     *     summary="getForm",
     *     description="getForm",
     *     @OA\Parameter(
     *         name="section",
     *         in="query",
     *         description="section for form",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="form data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"section":{},"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="section is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result section", value={"status": "error", "error":"Поле раздел обязательна для заполнения"},summary="Ошибка section"),
     *       )
     *     ),
     * )
     */

    public function getUserFields(Request $request)
    {
        $user = Auth::user();

        if (empty($request->section)) {
            $response['error'] = 'Поле секция обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }

        $formDataService = (new FormBuilderService(10, json_decode($user->data, true)));
        if (!empty($user->expansionData)) {
            $user->expansionData = json_decode($user->expansionData, true);
        } else {
            $user->expansionData = [];
        }
        if (!empty($user->errorData)) {
            $user->errorData = json_decode($user->errorData, true);
        } else {
            $user->errorData = [];
        }
        if (!empty($user->estateData)) {
            $user->estateData = json_decode($user->estateData, true);
        } else {
            $user->estateData = [];
        }
        if (!empty($user->requisitesData)) {
            $user->requisitesData = json_decode($user->requisitesData, true);
        } else {
            $user->requisitesData = [];
        }
        $formDataService->setDataUser($user->expansionData, $user->errorData, $user->estateData, $user->requisitesData);
        $response['result']['formData'] = $formDataService->createPersonalUserFormData($request->section);
        $response['result']['type'] = $formDataService->checkStatusForm(true);

        $response['result']['section'] = FormBuilderService::getUserMenu($user->errorData);
        $response['status'] = 'success';
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getUserPersonalMenu/",
     *     operationId="getUserPersonalMenu",
     *     tags={"Personal area"},
     *     summary="getUserPersonalMenu",
     *     description="getUserPersonalMenu",
     *     @OA\Response(
     *       response="200",
     *       description="menu data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"section":{}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getUserPersonalMenu(Request $request)
    {
        $user = Auth::user();
        if (!empty($user->errorData)) {
            $userError = json_decode($user->errorData, true);
        } else {
            $userError = [];
        }
        $response['result']['section'] = FormBuilderService::getUserMenu($userError);
        $response['status'] = 'success';
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveUserFields/",
     *     operationId="saveUserFields",
     *     tags={"Personal area"},
     *     summary="saveUserFields",
     *     description="saveUserFields Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"formData","section"},
     *                 @OA\Property(property="formData",type="json"),
     *                 @OA\Property(property="section",type="string"),
     *                 @OA\Property(property="dataId",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save form",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="formData or section is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result formData", value={"status": "error", "error":"Ничего не загружено"},summary="Ошибка formData"),
     *           @OA\Examples(example="result section", value={"status": "error", "error":"Поле раздел обязательна для заполнения"},summary="Ошибка section"),
     *       )
     *     ),
     * )
     */

    public function saveUserFields(Request $request)
    {
        $user = Auth::user();

        if (empty($request->section)) {
            $response['error'] = 'Поле раздел обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }

        if (!empty($request->formData)) {
            if ($userField = PersonalInfoSectionEnum::from($request->section)->getField()) {
                $userFieldData = [];
                if (!empty($user->$userField)) {
                    $userFieldData = json_decode($user->$userField, true);
                    if(!empty($request->dataId)){
                        $userFieldData[$request->dataId] = $request->formData;
                    }else{
                        $userFieldData[] = $request->formData;
                    }
                } else {
                    $userFieldData[] = $request->formData;
                }
                $user->$userField = json_encode($userFieldData);
                $user->save();
            } else {
                $userError = json_decode($user->errorData, true);
                $userData = json_decode($user->data, true);
                foreach ($request->formData as $k => $oneField) {
                    if ((!isset($userData[$k]) && !empty($userError[$k]) && !empty($oneField)) || (!empty($userData[$k]) && !empty($userError[$k]) && $userData[$k] != $oneField)) {
                        unset($userError[$k]);
                    }
                    $userData[$k] = $oneField;
                }
                $user->data = json_encode($userData);
                $user->errorData = json_encode($userError);
                $user->save();
            }
            $response['status'] = 'success';
        } else {
            $response['error'] = 'Ничего не загружено';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveUserImg/",
     *     operationId="saveUserImgPersonal",
     *     tags={"Personal area"},
     *     summary="saveUserImgPersonal",
     *     description="saveUserImgPersonal Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                  property="file",
     *                  type="file",
     *                  ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="file  info",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","resFile":"url file"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="file is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="error", value={"status": "error", "error":"Ничего не загружено"},summary="Нехватка полей"),
     *       )
     *     ),
     * )
     */

    public function saveUserImg(Request $request)
    {
        $user = Auth::user();
        if ($request->hasFile('file')) {
            $uploadFiles = $request->file('file');
            $extension = $uploadFiles->getClientOriginalExtension();
            $filename = Str::random(20) . '.' . $extension;
            if (!empty($user->img)) {
                Storage::disk('public')->delete($user->img);
            }
            $user->img = Storage::disk('public')->putFileAs('/source/userImg/' . $user->id, $uploadFiles, $filename, 'public');
            $user->save();
            $response['resFile'] = config('app.url') . Storage::url($user->img);
            $response['status'] = 'success';
        } else {
            $response['error'] = 'Ничего не загружено';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/setUserEmail/",
     *     operationId="setUserEmail",
     *     tags={"Personal area"},
     *     summary="setUserEmail",
     *     description="setUserEmail Endpoint",
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
     *       description="send email for verified",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="email is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "error", "error":"Email отсутствует"},summary="Нехватка полей"),
     *       )
     *     ),
     * )
     */

    public function setUserEmail(Request $request)
    {
        $user = Auth::user();
        if (!empty($request->email)) {
            $user->email = $request->email;
            $emailCodeService = new EmailVerifiedService($request->email);
            $response['result']['code'] = $emailCodeService->createCode();
            $response['status'] = $emailCodeService->status;
            if ($emailCodeService->status == 'success') {
                $user->save();
            }
            return response()->json($response, 200);
        } else {
            $response['error'] = 'Email отсутствует';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/checkEmailCode/",
     *     operationId="checkEmailCode",
     *     tags={"Personal area"},
     *     summary="checkEmailCode",
     *     description="checkEmailCode Endpoint",
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
     *       description="check email code success",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Code is empty",
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

    /**
     * @OA\Post(
     *     path="/api/personal/changeUserPhone/",
     *     operationId="changeUserPhone",
     *     tags={"Personal area"},
     *     summary="changeUserPhone",
     *     description="changeUserPhone Endpoint",
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
     *       description="start change phone",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="phone is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result phone", value={"status": "error", "error":"Поле телефон обязательна для заполнения"},summary="Ошибка кода"),
     *       )
     *     ),
     * )
     */

    public function changeUserPhone(Request $request)
    {
        if (empty($request->phone)) {
            $response['error'] = 'Поле телефон обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        $smsCodeService = new SmsCodeService($request->phone);
        $response['result']['code'] = $smsCodeService->createCode();
        $response['status'] = $smsCodeService->status;

        return response()->json($response, 200);
    }


    /**
     * @OA\Post(
     *     path="/api/personal/confirmChangeUserPhone/",
     *     operationId="confirmChangeUserPhone",
     *     tags={"Personal area"},
     *     summary="confirmChangeUserPhone",
     *     description="confirmChangeUserPhone Endpoint",
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
     *       description="confirm or error change phone",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Code or phone is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result phone", value={"status": "error", "error":"Поле телефон обязательна для заполнения"},summary="Ошибка кода"),
     *           @OA\Examples(example="result code", value={"status": "error", "error":"Поле код обязательна для заполнения"},summary="Ошибка кода"),
     *       )
     *     ),
     * )
     */

    public function confirmChangeUserPhone(Request $request)
    {
        if (empty($request->phone)) {
            $response['error'] = 'Поле телефон обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        if (empty($request->code)) {
            $response['error'] = 'Поле код обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        $smsCodeResult = (new SmsCodeService($request->phone, (int)$request->code))->checkCode();
        if ($smsCodeResult['status'] == 'success') {
            $user = Auth::user();
            $user->phone = $request->phone;
            $user->save();
            $response['status'] = $smsCodeResult['status'];
        } else {
            $response['result']['code'] = $smsCodeResult;
            $response['status'] = 'error';
        }
        return response()->json($response, 200);

    }

}
