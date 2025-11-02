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
        $dataSettings = [];
        $data = $request->input();
        if(!empty($data) && !empty($data['settings'])) {
            foreach ($data['settings'] as $k => $value) {
                if (!empty($value)) {
                    $dataSettings[] = [
                        'key'   => $k,
                        'value' => $value['value'],
                        'name' => $value['name']
                    ];
                }
            }
        }
        Setting::upsert($dataSettings,['key'],['value','name']);
        $response['status'] = 'success';
        return response()->json($response, 200);
    }


}
