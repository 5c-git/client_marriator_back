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
use App\Models\Document\Document;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;


class SettingsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getFromKey(Request $request)
    {
        $response = [
            'status' => 'error',
        ];
        if (!empty($request->key)) {
            $setting = Setting::query()->where('key', $request->key)->first();
            if ($setting) {
                $response['status'] = 'success';
                $response['value'] = $setting->value;
            }
        }
        return response()->json($response, 200);
    }

    public function getAll(Request $request){
        $response = [
            'status' => 'error',
        ];
        $settings = Setting::query()->get();
        if ($settings) {
            $response['status'] = 'success';
            $response['data'] = [];
            foreach ($settings as $setting){
                $response['data'][] = [
                    'key'=>$setting->key,
                    'value'=>$setting->value
                ];
            }
        }
        return response()->json($response, 200);
    }

}
