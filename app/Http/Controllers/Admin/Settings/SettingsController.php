<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function index(){
        $settings = Setting::query()->get();
        return view('admin.setting.setting', compact('settings'));
    }

    public function save(Request $request){
        Setting::query()->truncate();
        $dataSettings = [];
        if(!empty($request->settings) && !empty($request->settings['key']) && !empty($request->settings['value'])){
            foreach ($request->settings['key'] as $k=>$key){
                if(!empty($key) && !empty($request->settings['value'][$k])){
                    $dataSettings[$key] = [
                        'key' => $key,
                        'value' => $request->settings['value'][$k],
                    ];
                }
            }
        }
        Setting::query()->upsert($dataSettings,['key'],['value']);
        $response['status'] = 'success';
        return response()->json($response, 200);
    }


}
