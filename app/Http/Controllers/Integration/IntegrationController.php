<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;

class IntegrationController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function ping(Request $request){
        $response = [
            'status' => 'success'
        ];
        return response()->json($response,200);
    }

    public function updateUserData(Request $request){
        if(empty($request->userId) || User::query()->where('id', $request->userId)->doesntExist()){
            return response()->json([
                'errors' => 'user not found',
            ], 404);
        }
        $user = User::query()->find($request->userId);
        if(!empty($request->updateData)){
            $updateDataUser = json_decode($user->updateData, true);
            $userData = json_decode($user->data, true);
            if(empty($updateDataUser)){
                $updateDataUser = [];
            }
            foreach ($request->updateData as $k => $updateDatum) {
                if(!empty($updateDataUser[$k])) {
                    unset($updateDataUser[$k]);
                }
                $userData[$k] = $updateDatum;
                //обновлять из пришедшего или обновлять из updateData?
            }
            $user->updateData = json_encode($updateDataUser);
            $user->data = json_encode($userData);
        }
        if (!empty($request->errorData)) {
            $userError = json_decode($user->errorData, true);
            if (empty($userError)) {
                $userError = [];
            }
            $userError = array_merge($userError, $request->errorData);
            $user->data = json_encode($userError);
        }
        if (!empty($request->expansionData)) {
            $expansionData = json_decode($user->expansionData, true);
            if (empty($expansionData)) {
                $expansionData = [];
            }
            $expansionData = array_merge($expansionData, $request->expansionData);
            $user->expansionData = json_encode($expansionData);
        }
        $user->save();
        $response = [
            'status' => 'success'
        ];
        return response()->json($response,200);
    }
}
