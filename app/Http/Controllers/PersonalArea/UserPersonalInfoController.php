<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\FormBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Enum\Fields\PersonalInfoSectionEnum;


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
        $user->img = config('app.url').Storage::url($user->img);
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
     *     @OA\Response(
     *       response="200",
     *       description="form data",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"formData":{},"type":"needRequired|allowedNewStep",}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getUserFields(Request $request)
    {
        $user = Auth::user();

        $formDataService = (new FormBuilderService(10, json_decode($user->data,true)));
        if(!empty($user->expansionData)){
            $user->expansionData = json_decode($user->expansionData,true);
        }else{
            $user->expansionData = [];
        }
        if(!empty($user->errorData)){
            $user->errorData = json_decode($user->errorData,true);
        }else{
            $user->errorData = [];
        }
        $formDataService->setDataUser($user->expansionData,$user->errorData);
        $response['result']['formData'] = $formDataService->createPersonalUserFormData();
        $response['result']['type'] = $formDataService->checkStatusForm(true);

        foreach (PersonalInfoSectionEnum::options() as $k => $option) {
            $response['result']['section'][] = [
                'name' => PersonalInfoSectionEnum::from($option)->typeName(),
                'value' => $option
            ];
        }
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
     *                 @OA\Property(property="formData",type="json")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="save form",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{"type":"needRequired|allowedNewStep|addedNewFields",}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function saveUserFields(Request $request)
    {

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
        }

        return response()->json($response);
    }



}
