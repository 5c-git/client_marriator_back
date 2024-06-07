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
     * @OA\Post(
     *     path="/api/getForm/",
     *     operationId="getForm",
     *     tags={"form"},
     *     summary="getForm",
     *     description="getForm Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"step","currentStep"},
     *                 @OA\Property(property="step",type="number"),
     *                 @OA\Property(property="currentStep",type="number"),
     *                 @OA\Property(property="formData",type="json")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="form data",
     *       @OA\JsonContent()
     *     ),
     * )
     */

    public function getform(Request $request){
        if(!empty($request->step)){
            $step = $request->step;
        }else{
            $step = 1;
        }
        $user = $request->user();
        $userData = json_decode($user->data,true);
        $formData = (new FormBuilderService($step, $userData))->createFormData();
        $response['formData'] = $formData;
        $response['step'] = $step;
        return response()->json($response);
    }

    public function saveForm(Request $request)
    {

//        if(!empty($request->step)){
//            $step = $request->step;
//        }else{
//            $step = 1;
//        }
//        $user = $request->user();
//        if (!empty($request->currentStep)) {
//            $currentStep = $request->currentStep;
//        }else{
//           //error
//        }
//        if (!empty($request->formData)) {
//            $userData[$currentStep] = $request->formData;
//            $user->data = json_encode($userData);
//            $user->save();
//        }


        $step = 2;
        $currentStep = 2;
        if (!empty($request->step)) {
            $step = $request->step;
        }
        if (!empty($request->currentStep)) {
            $currentStep = $request->currentStep;
        }
        $formData = [];
        if (!empty($request->formData)) {
            $formData = $request->formData;
        }
        if (empty($request->phone)) {
            die();
        }
        if ($user = User::where('phone', $request->phone)->first()) {
            $userData = json_decode($user->data, true);
            $userData[$currentStep] = $formData;
        } else {
            $user = new User();
            $user->phone = $request->phone;
        }
        $user->data = json_encode($userData);
        $user->name = 'Тест';
        $user->email = 'dfvddfvdv@tt.tt';
        $user->password = 'dfvddfvdv@tt.tt';
        $user->save();
        echo "<pre>";
        var_dump((new FormBuilderService($step, $userData))->createFormData());
        echo "</pre>";
    }


    /**
     * @OA\Post(
     *     path="/api/saveFile/",
     *     operationId="saveFile",
     *     tags={"form"},
     *     summary="saveFile",
     *     description="saveFile Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
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
     *       @OA\JsonContent()
     *     ),
     * )
     */

    public function saveFile(Request $request)
    {
        $uploadFiles = $request->allFiles();
        $response['text1'] = 'под какими ключами прилители файлы (через запятую)  ' . implode(', ', array_keys($uploadFiles));
        $response['fileName'] = [];

        $files = [];
        if(!empty($uploadFiles)) {
            if(!is_array(current($uploadFiles))){
                $files[] = current($uploadFiles);
            }else{
                $files = current($uploadFiles);
            }
            foreach ($files as $uploadFile) {
                $response['fileName'][] = $uploadFile->getClientOriginalName();
            }
            $userId = 1;
            $createFileService = new CreatePdfFileService($files,$userId);
            if(!empty($createFileService->mergeFilePath) && empty($createFileService->error)){
                $response['resFile'] = $createFileService->mergeFilePath;
            }else{
                $response['error'] = $createFileService->error;
            }
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
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"file"},
     *                 @OA\Property(
     *                  property="file",
     *                  type="file",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="file info",
     *       @OA\JsonContent()
     *     ),
     * )
     */

    public function saveUserImg(Request $request){
        $user = $request->user();
        if($request->hasFile('file')) {
            $uploadFiles = $request->file('file');
            $extension = $uploadFiles->getClientOriginalExtension();
            $filename = Str::random(20) . '.'.$extension;
            if(!empty($user->img)){
                Storage::disk('public')->delete($user->img);
            }
            $user->img = Storage::disk('public')->putFileAs('/source/userImg/' . $user->id, $uploadFiles, $filename, 'public');
            $user->save();
            $response['resFile'] = Storage::url($user->img);
        }else{
            $response['error'] = 'ничего не загружено';
        }
        return response()->json($response);
    }

}
