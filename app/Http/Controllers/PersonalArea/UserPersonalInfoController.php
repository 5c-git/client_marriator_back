<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\DocumentServices\RecognitionDocumentService;
use App\Services\FormBuilderService;
use App\Services\OneC\OneCServices;
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
     *     summary="Получить данные из профиля пользователя",
     *     description="Метод получения данных из профиля пользователя",
     *     @OA\Response(
     *       response="200",
     *       description="Данные из профиля успешно получены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"userData":{},}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getUserInfo(Request $request)
    {
        $user = Auth::user();
        if($user->img) {
            $user->img = config('app.url') . Storage::url($user->img);
        }
        if(strripos($user->email, 'mariator.ru') === false) {

        }else{
            $user->email = '';
        }
        unset($user->change_fields);
        unset($user->date_for_send);
        $response['result']['userData'] = $user->toArray();
        $response['result']['userData']['roles'] = RoleResource::collection($user->roles);
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getUserFields/",
     *     operationId="getUserFields",
     *     tags={"Personal area"},
     *     summary="Получить данные из подразделов профиля пользователя",
     *     description="Метод получения данных из подразделов профиля пользователя",
     *     @OA\Parameter(
     *         name="section",
     *         in="query",
     *         description="Раздел 'Мой профиль': номер подраздела",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Данные успешно получены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"section":{},"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Неуспешный запрос",
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
        if (!empty($user->updateData)) {
            $user->updateData = json_decode($user->updateData, true);
        } else {
            $user->updateData = [];
        }

        if (!empty($user->change_fields)) {
            $user->change_fields = json_decode($user->change_fields, true);
        } else {
            $user->change_fields = [];
        }
        if(!is_array($user->errorData)){
            $user->errorData = json_encode([]);
            $user->save();
            $user->errorData = [];
        }
        if(!is_array($user->updateData)){
            $user->updateData = json_encode([]);
            $user->save();
            $user->updateData = [];
        }


        $formDataService->setDataUser($user->expansionData, $user->errorData,$user->updateData,$user->change_fields);
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
     *     summary="Получить список подразделов раздела 'Мой профиль'",
     *     description="Метод получения списка подразделов 'Моего профиля'",
     *     @OA\Response(
     *       response="200",
     *       description="Данные успешно получены",
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
     *     summary="Сохранить данные в профиль пользователя",
     *     description="Метод добавления данных в профиль пользователя",
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
     *       description="Сохранить форму",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="FormData пуст",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result formData", value={"status": "error", "error":"Ничего не загружено"},summary="Ошибка formData"),
     *       )
     *     ),
     * )
     */

    public function saveUserFields(Request $request)
    {
        $user = Auth::user();
        $change_fields = [];
        $change_fieldsUp = [];
        if (!empty($request->formData)) {
            $userError = json_decode($user->errorData, true);
            $change_fieldsUser = json_decode($user->change_fields, true);
            $userData = json_decode($user->data, true);
            foreach ($request->formData as $k => $oneField) {
                if ((!isset($userData[$k]) && !empty($userError[$k]) && !empty($oneField)) || (!empty($userData[$k]) && !empty($userError[$k]) && $userData[$k] != $oneField)) {
                    unset($userError[$k]);
                }

                if ((!isset($userData[$k]) && !empty($oneField)) || (!empty($userData[$k]) && $userData[$k] != $oneField || !empty($change_fieldsUser[$k]))) {
                    $change_fields[$k] = $oneField;
                }
                $userData[$k] = $oneField;
            }
            //$user->data = json_encode($userData);
            if(!empty($change_fieldsUser)){
                foreach ($change_fieldsUser as $k => &$oneUpdate){
                    if(!empty($change_fields[$k]) && $change_fields[$k] != $oneUpdate){
                        $oneUpdate = $change_fields[$k];
                        unset($change_fields[$k]);
                    }
                }
            }else{
                $change_fieldsUser = [];
            }
            $change_fieldsUp = array_merge($change_fields,$change_fieldsUser);

//            $updateResult = (new OneCServices($user))->updateUserData($change_fields);
//            if($updateResult->status) {
                $user->change_fields = json_encode($change_fieldsUp);
           // }

            if(empty($userError) || !is_array($userData)){
                $userData = [];
            }
            if(empty($user->date_for_send)){
                $user->date_for_send = Carbon::now();
            }
            $user->errorData = json_encode($userError);
            $user->save();
            if(!empty($userData)) {
                (new RecognitionDocumentService($userData, $user))->createDocumentForRecognition();
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
     *     summary="Сохранить фото в профиле пользователя",
     *     description="Метод сохранения фото в профиле пользователя",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                  property="file",
     *                  type="file",
	                    description="Файл в формате jpg, png, pdf не более 6MB",
     *                  ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Файл успешно загружен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","resFile":"url file"},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Неуспешный запрос",
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
     *     summary="Поменять email в профиле пользователя",
     *     description="Метод сохранения email в профиле пользователя",
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
     *       description="Еmail отправлен, получен код для подтверждения",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Еmail не указан",
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
     *     summary="Отправить код для подтверждения email",
     *     description="Метод отправки кода для подтверждения email",
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
     *       description="Код успешно отправлен",
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

    /**
     * @OA\Post(
     *     path="/api/personal/changeUserPhone/",
     *     operationId="changeUserPhone",
     *     tags={"Personal area"},
     *     summary="Сохранить номер телефона в профиле пользователя",
     *     description="Метод сохранения номера телефона в профиле пользователя",
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
     *           @OA\Examples(example="result", value={"status": "success","result":{"code":{"status":"exists|success","ttl":"120 числовое поле если статус exists","code":"sms код для теста"}}},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Номер телефона не указан",
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
     *     summary="Подтвердить номер телефона в профиле пользователя",
     *     description="Метод подтверждения номера телефона в профиле пользователя",
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
     *       description="Подтверждение или ошибка смены телефона",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *           @OA\Examples(example="result error", value={"status": "error","result":{"code":{"status":"error|notExists"},}},summary="Ошибка"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Код не заполнено",
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

    /**
     * @OA\Get(
     *     path="/api/personal/getRequisitesData/",
     *     operationId="getRequisitesData",
     *     tags={"Personal area"},
     *     summary="Получить банковские реквизиты пользователя",
     *     description="Метод получения реквизитов пользователя",
     *     @OA\Response(
     *       response="200",
     *       description="Реквизиты пользователя",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{}},summary="Успех"),
     *       )
     *     ),
     * )
     */
    public function getRequisitesData(Request $request)
    {
        $user = Auth::user();
        $responseData = [];
        if (!empty($user->requisitesData)) {
            $requisitesData = json_decode($user->requisitesData, true);
            $requisitesData = array_values($requisitesData);
            foreach ($requisitesData as $k=>$requisitesDataOne){
                $responseData[$k] = $requisitesDataOne;
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
     *     summary="Получить данные об имуществе пользователя",
     *     description="Метод получения данных об имуществе пользователя",
     *     @OA\Response(
     *       response="200",
     *       description="Получены данные имущества пользователя",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getEstateData(Request $request)
    {
        $user = Auth::user();
        $responseData = [];
        if (!empty($user->estateData)) {
            $estateData = json_decode($user->estateData, true);
            $i = 0;
            foreach ($estateData as $k=>$estateDataOne){
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
     *     summary="Сохранить банковские реквизиты пользователя",
     *     description="Метод добавления банковских реквизитов пользователя",
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
     *       description="Реквизиты сохранены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка данных",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле дата обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function saveRequisitesData(Request $request)
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
        if (isset($request->dataId)) {
            $requisitesData[$request->dataId] = $request->data;
        } else {
            $requisitesData[] = $request->data;
        }
        $requisitesData = array_values($requisitesData);
        if((new OneCServices($user))->sendUpdateUserRequisites($requisitesData)->status){
            $user->requisitesData = json_encode($requisitesData);
            $user->save();
        };
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveEstateData/",
     *     operationId="saveEstateData",
     *     tags={"Personal area"},
     *     summary="Сохранить имущество пользователя",
     *     description="Метод сохранения имущества пользователя",
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
     *       description="Имущество сохранено",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка данных",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле дата обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */
    public function saveEstateData(Request $request)
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
     *     summary="Получить данные из раздела 'Виды деятельности' в профиле пользователя",
     *     description="Метод получения данных из раздела 'Виды деятельности'",
     *     @OA\Parameter(
     *         name="step",
     *         in="query",
     *         description="шаг 1-3",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Данные из раздела 'Виды деятельности' успешно получены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"step":{},"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Поле step не может быть больше 3",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result section", value={"status": "error", "error":"Поле step не может быть больше 3"},summary="Ошибка section"),
     *       )
     *     ),
     * )
     */

    public function getformActivities(Request $request)
    {
        $user = Auth::user();
        if($request->step<=3 || empty($request->step)) {
            if (!empty($request->step)) {
                $step = (int)$request->step;
            } else {
                $step = 1;
            }

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
            if (!empty($user->updateData)) {
                $user->updateData = json_decode($user->updateData, true);
            } else {
                $user->updateData = [];
            }

            if (!empty($user->change_fields)) {
                $user->change_fields = json_decode($user->change_fields, true);
            } else {
                $user->change_fields = [];
            }
            if(!is_array($user->errorData)){
                $user->errorData = json_encode([]);
                $user->save();
                $user->errorData = [];
            }
            if(!is_array($user->updateData)){
                $user->updateData = json_encode([]);
                $user->save();
                $user->updateData = [];
            }

            if (!empty($user->data)) {
                $userData = json_decode($user->data, true);
            } else {
                $userData = [];
            }
            $userData = $user->change_fields + $userData;
            $formDataService = (new FormBuilderService($step, $userData));
            $response['result']['formData'] = $formDataService->createFormData($user->expansionData, $user->errorData,$user->updateData);
            $response['result']['step'] = $step;
            $response['result']['type'] = $formDataService->checkStatusForm(true);
            $response['status'] = 'success';
            if(!empty($userData)) {
                (new RecognitionDocumentService($userData, $user))->createDocumentForRecognition();
            }
        }else{
            $response['error'] = 'Поле step не может быть больше 3';
            $response['status'] = 'error';
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/saveUserFieldsActivities/",
     *     operationId="saveUserFieldsActivities",
     *     tags={"Personal area"},
     *     summary="Сохранить данные в разделе 'Виды деятельности'",
     *     description="Метод сохранения/ изменения данных в разделе 'Виды деятельности'",
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
     *       description="Данные сохранены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"step": 1,"type":"needRequired|allowedNewStep|addedNewFields"}},summary="Успех"),
     *       )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ничего не загружено",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result formData", value={"status": "error", "error":"Ничего не загружено"},summary="Ошибка formData"),
     *       )
     *     ),
     * )
     */

    public function saveUserFieldsActivities(Request $request)
    {
        $user = Auth::user();
        $change_fields = [];
        $change_fieldsUp = [];
        if (!empty($request->formData) && $request->step) {
            $userError = json_decode($user->errorData, true);
            $change_fieldsUser = json_decode($user->change_fields, true);
            $userData = json_decode($user->data, true);
            foreach ($request->formData as $k => $oneField) {
                if ((!isset($userData[$k]) && !empty($userError[$k]) && !empty($oneField)) || (!empty($userData[$k]) && !empty($userError[$k]) && $userData[$k] != $oneField)) {
                    unset($userError[$k]);
                }
                if ((!isset($userData[$k]) && !empty($oneField)) || (!empty($userData[$k]) && $userData[$k] != $oneField || !empty($change_fieldsUser[$k]))) {
                    $change_fields[$k] = $oneField;
                }
                $userData[$k] = $oneField;
            }

            if(!empty($change_fieldsUser)){
                foreach ($change_fieldsUser as $k => &$oneUpdate){
                    if(!empty($change_fields[$k]) && $change_fields[$k] != $oneUpdate){
                        $oneUpdate = $change_fields[$k];
                        unset($change_fields[$k]);
                    }
                }
            }else{
                $change_fieldsUser = [];
            }

            if(!empty($userData)) {
                (new RecognitionDocumentService($userData, $user))->createDocumentForRecognition();
            }

            $change_fieldsUp = array_merge($change_fields,$change_fieldsUser);

//            $updateResult = (new OneCServices($user))->updateUserData($change_fields);
//            if($updateResult->status) {
                $user->change_fields = json_encode($change_fieldsUp);
           // }
           // $user->data = json_encode($userData);
            $user->errorData = json_encode($userError);
            if(empty($user->date_for_send)){
                $user->date_for_send = Carbon::now();
            }
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
     *     summary="Удалить имущество пользователя",
     *     description="Метод удаления имущества",
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
     *       description="Данные об имуществе удалены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка данных",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле номер имущества обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function deleteEstate(Request $request){
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
     *     summary="Удалить банковские реквизиты пользователя",
     *     description="Метод удаления банковских реквизитов",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"dataId"},
     *                 @OA\Property(property="data",type="number"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Банковские реквизиты удалены",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успешный запрос"),
     *        )
     *     ),
     *     @OA\Response(
     *       response="417",
     *       description="Ошибка данных",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result data", value={"status": "error", "error":"Поле номер реквизитов обязательна для заполнения"},summary="Ошибка данных"),
     *       )
     *     ),
     * )
     */

    public function deleteRequisite(Request $request){
        $user = Auth::user();
        if (!isset($request->dataId)) {
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
        $requisitesData = array_values($requisitesData);
        if((new OneCServices($user))->sendUpdateUserRequisites($requisitesData)->status){
            $user->requisitesData = json_encode($requisitesData);
            $user->save();
        };
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getBic/",
     *     operationId="getBic",
     *     tags={"Personal area"},
     *     summary="Получить данные банковского БИКа пользователя",
     *     description="Метод получения БИКа пользователя",
     *     @OA\Response(
     *       response="200",
     *       description="Бик получен",
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
            $response['result']['bankData'][] = ['value' => $bank->uuid, 'label' => $bank->name, 'bic' => $bank->bic, 'disabled' => false];
        }
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/getMapField/",
     *     operationId="getMapField",
     *     tags={"Personal area"},
     *     summary="Получить адрес и радиус поиска работы пользователя",
     *     description="Метод получения адреса и радиус поиска работы",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
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
        $response['result']['latitude'] = $user->latitude;
        $response['result']['longitude'] = $user->longitude;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/setMapField/",
     *     operationId="setMapField",
     *     tags={"Personal area"},
     *     summary="Отправить данные адреса и радиуса поиска работы",
     *     description="Метод добавления данных адреса и радиуса поиска работы",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="mapAddress",type="string"),
     *                 @OA\Property(property="mapRadius",type="string"),
     *                 @OA\Property(property="latitude",type="string"),
     *                 @OA\Property(property="longitude",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Данные добавлены",
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
        if(!empty($request->latitude)){
            $user->latitude = $request->latitude;
        }
        if(!empty($request->longitude)){
            $user->longitude = $request->longitude;
        }
        $user->save();
        $response['result']['mapAddress'] = $user->mapAddress;
        $response['result']['mapRadius'] = $user->mapRadius;
        $response['result']['latitude'] = $user->latitude;
        $response['result']['longitude'] = $user->longitude;
        $response['status'] = 'success';
        return response()->json($response, 200);
    }



}
