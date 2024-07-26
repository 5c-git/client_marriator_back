<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\FormBuilderService;
use App\Services\CreatePdfFileService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\OneC\OneCServices;
use App\Services\ApiTokenService\ApiTokenService;

class FormController extends Controller
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
     *     path="/api/getForm/",
     *     operationId="getForm",
     *     tags={"form"},
     *     summary="getForm",
     *     description="getForm Endpoint",
     *     @OA\Parameter(
     *         name="step",
     *         in="query",
     *         description="step for form",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="form data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"step":1,"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getform(Request $request)
    {
        $user = Auth::user();
        if(!$user->finishRegister) {
            if (!empty($request->step)) {
                $step = (int)$request->step;
            } else {
                $step = 1;
            }
            $user = Auth::user();
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
            $response['status'] = 'error';
        }

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/saveForm/",
     *     operationId="saveForm",
     *     tags={"form"},
     *     summary="saveForm",
     *     description="saveForm Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"step"},
     *                 @OA\Property(property="step",type="number"),
     *                 @OA\Property(property="formData",type="json")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save form",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"step":1,"type":"needRequired|allowedNewStep|addedNewFields",}},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error", "error":"Поле step обязательна для заполнения"},summary="Нехватка полей"),
     *       )
     *     ),
     * )
     */

    public function saveForm(Request $request)
    {
        //$this->setUser();
        $response = [];
        $user = Auth::user();
        if(!$user->finishRegister) {
            if (!empty($request->step)) {
                $step = $request->step;
                if (!empty($request->formData)) {
                    if (!empty($user->data)) {
                        $userData = json_decode($user->data, true);
                    } else {
                        $userData = [];
                    }
                    $userData[$step] = $request->formData;
                    $user->data = json_encode($userData);
                    $user->save();
                }

                if (!empty($user->data)) {
                    $formData = json_decode($user->data, true);
                } else {
                    $formData = [];
                }

                $formDataService = (new FormBuilderService($step, $formData));
                $formDataService->getStepField();
                $response['result'] = [
                    'step' => $step,
                    'type' => $formDataService->checkStatusForm()
                ];
                $response['status'] = 'success';
            } else {
                $response['error'] = 'Поле step обязательна для заполнения';
                $response['status'] = 'error';
            }
        }else{
            $response['status'] = 'error';
        }
        return response()->json($response);
    }

    protected function setUser(){
        $user = User::where('email','ilyaDevmarriator@gmail.com')->first();
        Auth::login($user);
    }


    /**
     * @OA\Post(
     *     path="/api/saveFile/",
     *     operationId="saveFile",
     *     tags={"form"},
     *     summary="saveFile",
     *     description="saveFile Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file[]"},
     *                 @OA\Property(
     *                  property="file[]",
     *                  type="array",
     *                  @OA\Items(type="file")),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="file info",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","resFile":"url file"},summary="Успех"),
     *           @OA\Examples(example="result error", value={"status": "error", "error":"Текст"},summary="Ошибка формирования файла"),
     *       )
     *     ),
     * )
     */

    public function saveFile(Request $request)
    {
        $user = Auth::user();
        if(!$user->finishRegister) {
            $uploadFiles = $request->allFiles();
            $files = [];
            if (!empty($uploadFiles)) {
                if (!is_array(current($uploadFiles))) {
                    $files[] = current($uploadFiles);
                } else {
                    $files = current($uploadFiles);
                }
                //foreach ($files as $uploadFile) {
                // $response['fileName'][] = $uploadFile->getClientOriginalName();
                //}
                $userId = Auth::id();
                $createFileService = new CreatePdfFileService($files, $userId);
                if (!empty($createFileService->mergeFilePath) && empty($createFileService->error)) {
                    $response['resFile'] = config('app.url').$createFileService->mergeFilePath;
                    $response['status'] = 'success';
                } else {
                    $response['error'] = $createFileService->error;
                    $response['status'] = 'error';
                }
            }
        }else{
            $response['status'] = 'error';
        }
        return response()->json($response);
    }


    /**
     * @OA\Post(
     *     path="/api/saveUserImg/",
     *     operationId="saveUserImg",
     *     tags={"form"},
     *     summary="saveUserImg",
     *     description="saveUserImg Endpoint",
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
     *           @OA\Examples(example="error", value={"status": "error", "error":"Ничего не загружено"},summary="Нехватка полей"),
     *       )
     *     ),
     * )
     */

    public function saveUserImg(Request $request){
        //$this->setUser();
        $user = Auth::user();
        if(!$user->finishRegister) {
            if ($request->hasFile('file')) {
                $uploadFiles = $request->file('file');
                $extension = $uploadFiles->getClientOriginalExtension();
                $filename = Str::random(20) . '.' . $extension;
                if (!empty($user->img)) {
                    Storage::disk('public')->delete($user->img);
                }
                $user->img = Storage::disk('public')->putFileAs('/source/userImg/' . $user->id, $uploadFiles, $filename, 'public');
                $user->save();
                $response['resFile'] = config('app.url').Storage::url($user->img);
                $response['status'] = 'success';
            } else {
                $response['error'] = 'Ничего не загружено';
                $response['status'] = 'error';
            }
        }else{
            $response['status'] = 'error';
        }
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/finishRegister/",
     *     operationId="finishRegister",
     *     tags={"form"},
     *     summary="finishRegister",
     *     description="finishRegister Endpoint",
     *     @OA\Response(
     *       response="200",
     *       description="finish register",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"token": {"token_type":"Bearer","expires_in":"числовое значение в секундах время жизни access_token","access_token":"токен доступа","refresh_token":"токен восстановления access_token"},}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function finishRegister(Request $request){
        $user = Auth::user();
        if(!$user->finishRegister) {
            $user->finishRegister = true;
            $user->save();

            $registerResult = (new OneCServices($user))->sendRegister();

            if($registerResult->statusRegister) {
                $user->confirmRegister = true;
                $user->data = json_encode(array_merge(...json_decode($user->data,true)));
                $user->save();
                $apiTokenService = new ApiTokenService($user);
                $token = $apiTokenService->createToken(['checkPin']);

                $response['result']['token'] = $token;
                $response['status'] = 'success';
            }else{
                $response['status'] = 'error';
            }
        }else{
            $response['status'] = 'error';
        }
        return response()->json($response);
    }

}
