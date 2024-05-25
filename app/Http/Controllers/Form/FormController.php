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
     *     path="/api/getForm",
     *     operationId="getForm",
     *     tags={"getForm"},
     *     summary="getForm",
     *     description="getForm Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"step"},
     *                 @OA\Property(property="step",type="number"),
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

    public function getForm(Request $request)
    {
        $step = 2;
        if (!empty($request->step)) {
            $step = $request->step;
        }
        $formData = [];
        if (!empty($request->formData)) {
            $formData = $request->formData;
        }
        if (empty($request->phone)) {
            die();
        }
        if ($user = User::where('phone', $request->phone)->first()) {
            $formData = array_merge(json_decode($user->data, true), $formData);
        } else {
            $user = new User();
            $user->phone = $request->phone;
        }
        $user->data = json_encode($formData);
        $user->name = 'Тест';
        $user->email = 'dfvddfvdv@tt.tt';
        $user->password = 'dfvddfvdv@tt.tt';
        $user->save();
//        $formData['1111'] = 1;
//        $formData['1112'] = 'city1';
        echo "<pre>";
        var_dump((new FormBuilderService($step, $formData))->createFormData());
        echo "</pre>";

    }

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
            if(!empty($createFileService->mergeFilePath)){
                $response['resFile'] = $createFileService->mergeFilePath;
            }else{
                $response['error'] = 'хз';
            }
        }


        return response()->json($response);

    }

}
