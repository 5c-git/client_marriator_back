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

    public function getUserInfo(Request $request)
    {
        $user = Auth::user();
        $response['result']['userData'] = $user->toArray();
        $response['status'] = 'success';
        return response()->json($response, 200);
    }

    public function getUserFields(Request $request)
    {
        $user = Auth::user();

        $formDataService = (new FormBuilderService(10, $user->data));
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

    public function saveUserFields(Request $request)
    {

    }

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
