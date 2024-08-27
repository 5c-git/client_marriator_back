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
use App\Models\Fields\Directory\Bank;


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

    public function getUserInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if($user->img) {
            $user->img = config('app.url') . Storage::url($user->img);
        }
        if(strripos($user->email, 'mariator.ru') === false) {

        }else{
            $user->email = '';
        }
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

    public function getUserFields(Request $request): \Illuminate\Http\JsonResponse
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
        if(!is_array($user->errorData)){
            $user->errorData = json_encode([]);
            $user->save();
            $user->errorData = [];
        }


        $formDataService->setDataUser($user->expansionData, $user->errorData);
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

    public function getUserPersonalMenu(Request $request): \Illuminate\Http\JsonResponse
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
     *                 required={"formData"},
     *                 @OA\Property(property="formData",type="json"),
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
     *       description="formData is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result formData", value={"status": "error", "error":"Ничего не загружено"},summary="Ошибка formData"),
     *       )
     *     ),
     * )
     */

    public function saveUserFields(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!empty($request->formData)) {
            $userError = json_decode($user->errorData, true);
            $userData = json_decode($user->data, true);
            foreach ($request->formData as $k => $oneField) {
                if ((!isset($userData[$k]) && !empty($userError[$k]) && !empty($oneField)) || (!empty($userData[$k]) && !empty($userError[$k]) && $userData[$k] != $oneField)) {
                    unset($userError[$k]);
                }
                $userData[$k] = $oneField;
            }
            $user->data = json_encode($userData);
            if(empty($userError) || !is_array($userData)){
                $userData = [];
            }
            $user->errorData = json_encode($userError);
            $user->save();

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

    public function saveUserImg(Request $request): \Illuminate\Http\JsonResponse
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

    public function setUserEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!empty($request->email) && (User::where('email',$request->email)->doesntExist() || $user->email == $request->email)) {
            $user->email = $request->email;
            $emailCodeService = new EmailVerifiedService($request->email);
            $response['result']['code'] = $emailCodeService->createCode();
            $response['status'] = $emailCodeService->status;
            if ($emailCodeService->status == 'success') {
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

    public function checkEmailCode(Request $request): \Illuminate\Http\JsonResponse
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

    public function changeUserPhone(Request $request): \Illuminate\Http\JsonResponse
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

    public function confirmChangeUserPhone(Request $request): \Illuminate\Http\JsonResponse
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

    /**
     * @OA\Get(
     *     path="/api/personal/getRequisitesData/",
     *     operationId="getRequisitesData",
     *     tags={"Personal area"},
     *     summary="getRequisitesData",
     *     description="getRequisitesData",
     *     @OA\Response(
     *       response="200",
     *       description="Requisites data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{}},summary="Успех"),
     *       )
     *     ),
     * )
     */
    public function getRequisitesData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $responseData = [];
        if (!empty($user->requisitesData)) {
            $requisitesData = json_decode($user->requisitesData, true);
            $i=0;
            foreach ($requisitesData as $requisitesDataOne){
                $responseData[$i] = $requisitesDataOne;
                $i++;
            }
        }
        $response['result'] = $responseData;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getEstateData/",
     *     operationId="getEstateData",
     *     tags={"Personal area"},
     *     summary="getEstateData",
     *     description="getEstateData",
     *     @OA\Response(
     *       response="200",
     *       description="Estate data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getEstateData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $responseData = [];
        if (!empty($user->estateData)) {
            $estateData = json_decode($user->estateData, true);
            $i=0;
            foreach ($estateData as $estateDataOne){
                $responseData[$i] = $estateDataOne;
                $i++;
            }
        }
        $response['result'] = $responseData;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveRequisitesData/",
     *     operationId="saveRequisitesData",
     *     tags={"Personal area"},
     *     summary="saveRequisitesData",
     *     description="saveRequisitesData Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"data"},
     *                 @OA\Property(property="data",type="json"),
     *                 @OA\Property(property="dataId",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save requisites",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="data is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле дата обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function saveRequisitesData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (empty($request->data)) {
            $response['error'] = 'Поле дата обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        if (!empty($user->requisitesData)) {
            $requisitesData = json_decode($user->requisitesData, true);
        } else {
            $requisitesData = [];
        }
        if (!empty($request->dataId)) {
            $requisitesData[$request->dataId] = $request->data;
        } else {
            $requisitesData[] = $request->data;
        }
        $user->requisitesData = json_encode($requisitesData);
        $user->save();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveEstateData/",
     *     operationId="saveEstateData",
     *     tags={"Personal area"},
     *     summary="saveEstateData",
     *     description="saveEstateData Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"data"},
     *                 @OA\Property(property="data",type="json"),
     *                 @OA\Property(property="dataId",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save requisites",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="data is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле дата обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */
    public function saveEstateData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (empty($request->data)) {
            $response['error'] = 'Поле дата обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        if (!empty($user->estateData)) {
            $estateData = json_decode($user->estateData, true);
        } else {
            $estateData = [];
        }
        if (!empty($request->dataId)) {
            $estateData[$request->dataId] = $request->data;
        } else {
            $estateData[] = $request->data;
        }
        $user->estateData = json_encode($estateData);
        $user->save();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getformActivities/",
     *     operationId="getformActivities",
     *     tags={"Personal area"},
     *     summary="getformActivities",
     *     description="getformActivities",
     *     @OA\Parameter(
     *         name="step",
     *         in="query",
     *         description="step",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="form data Activities",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"step":{},"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="step error",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result section", value={"status": "error", "error":"Поле step не может быть больше 3"},summary="Ошибка section"),
     *       )
     *     ),
     * )
     */

    public function getFormActivities(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if($request->step<=3 || empty($request->step)) {
            if (!empty($request->step)) {
                $step = (int)$request->step;
            } else {
                $step = 1;
            }
            if (!empty($user->data)) {
                $userData = json_decode($user->data, true);
            } else {
                $userData = [];
            }
            $formDataService = (new FormBuilderService($step, $userData));
            $response['result']['formData'] = $formDataService->createFormData();
            $response['result']['step'] = $step;
            $response['result']['type'] = $formDataService->checkStatusForm(true);
            $response['status'] = 'success';
        }else{
            $response['error'] = 'Поле step не может быть больше 3';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveUserFieldsActivities/",
     *     operationId="saveUserFieldsActivities",
     *     tags={"Personal area"},
     *     summary="saveUserFieldsActivities",
     *     description="saveUserFieldsActivities Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"formData","step"},
     *                 @OA\Property(property="formData",type="json"),
     *                 @OA\Property(property="step",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save form",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"step": 1,"type":"needRequired|allowedNewStep|addedNewFields"}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="formData is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result formData", value={"status": "error", "error":"Ничего не загружено"},summary="Ошибка formData"),
     *       )
     *     ),
     * )
     */

    public function saveUserFieldsActivities(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!empty($request->formData) && $request->step) {
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

            $formDataService = (new FormBuilderService($request->step, $userData));
            $formDataService->getStepField();
            $response['result'] = [
                'step' => $request->step,
                'type' => $formDataService->checkStatusForm(false,$request->formData)
            ];
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
     *     path="/api/personal/deleteEstate/",
     *     operationId="deleteEstate",
     *     tags={"Personal area"},
     *     summary="deleteEstate",
     *     description="deleteEstate Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"data"},
     *                 @OA\Property(property="data",type="json"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="delete requisites",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="dataId is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле номер имущества обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function deleteEstate(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (empty($request->dataId)) {
            $response['error'] = 'Поле номер имущества обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        if (!empty($user->estateData)) {
            $estateData = json_decode($user->estateData, true);
            unset($estateData[$request->dataId]);
        } else {
            $estateData = [];
        }
        $user->estateData = json_encode($estateData);
        $user->save();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/deleteRequisite/",
     *     operationId="deleteRequisite",
     *     tags={"Personal area"},
     *     summary="deleteRequisite",
     *     description="deleteRequisite Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"data"},
     *                 @OA\Property(property="data",type="json"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="delete requisites",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="dataId is empty",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле номер реквизитов обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function deleteRequisite(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (empty($request->dataId)) {
            $response['error'] = 'Поле номер реквизитов обязательна для заполнения';
            $response['status'] = 'error';
            return response()->json($response, 417);
        }
        if (!empty($user->requisitesData)) {
            $requisitesData = json_decode($user->requisitesData, true);
            unset($requisitesData[$request->dataId]);
        } else {
            $requisitesData = [];
        }
        $user->requisitesData = json_encode($requisitesData);
        $user->save();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getBic/",
     *     operationId="getBic",
     *     tags={"Personal area"},
     *     summary="getBic",
     *     description="getBic Endpoint",
     *     @OA\Response(
     *       response="200",
     *       description="get bic",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result": {"bankData": {"value": "значение uuid","label": "название","disabled": "false",},}},summary="Успешный запрос"),
     *       )
     *     ),
     * )
     */

    public function getBic(): \Illuminate\Http\JsonResponse
    {
        $banks = Bank::where('active',true)->get();
        $response['result']['bankData'] = [];
        foreach ($banks as $bank) {
            $response['result']['bankData'][] = ['value' => $bank->uuid, 'label' => $bank->name, 'disabled' => false];
        }
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getMapField/",
     *     operationId="getMapField",
     *     tags={"Personal area"},
     *     summary="getMapField",
     *     description="getMapField Endpoint",
     *     @OA\Response(
     *       response="200",
     *       description="get Map Field",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success", "result": {"mapAddress": "string map address","mapRadius": "string map radius"}},summary="Успешный запрос"),
     *       )
     *     ),
     * )
     */

    public function getMapField(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $response['result']['mapAddress'] = $user->mapAddress;
        $response['result']['mapRadius'] = $user->mapRadius;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/setMapField/",
     *     operationId="setMapField",
     *     tags={"Personal area"},
     *     summary="setMapField",
     *     description="setMapField Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="mapAddress",type="string"),
     *                 @OA\Property(property="mapRadius",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="setMapField",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success", "result": {"mapAddress": "string map address","mapRadius": "string map radius"}},summary="Успешный запрос"),
     *        )
     *     ),
     * )
     */
    public function setMapField(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if(!empty($request->mapAddress)){
            $user->mapAddress = $request->mapAddress;
        }
        if(!empty($request->mapRadius)){
            $user->mapRadius = $request->mapRadius;
        }
        $response['result']['mapAddress'] = $user->mapAddress;
        $response['result']['mapRadius'] = $user->mapRadius;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }



}
