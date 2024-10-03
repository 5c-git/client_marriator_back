<?php

namespace App\Http\Controllers\Admin\Certificates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificates;

class CertificatesController extends Controller
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
        $certificates = Certificates::query()->get();
        return view('admin.certificates.certificates', compact('certificates'));
    }

    public function save(Request $request){
        Certificates::query()->truncate();
        $dataCertificates = [];
        if(!empty($request->certificates) && !empty($request->certificates['key']) && !empty($request->certificates['value'])){
            foreach ($request->certificates['key'] as $k=>$key){
                if(!empty($key) && !empty($request->certificates['value'][$k])){
                    $dataCertificates[$key] = [
                        'key' => $key,
                        'value' => $request->certificates['value'][$k],
                    ];
                }
            }
        }
        Certificates::query()->upsert($dataCertificates,['key'],['value']);
        $response['status'] = 'success';
        return response()->json($response, 200);
    }


}
