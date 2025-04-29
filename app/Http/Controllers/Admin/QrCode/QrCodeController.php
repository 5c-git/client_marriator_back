<?php

namespace App\Http\Controllers\Admin\QrCode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class QrCodeController extends Controller
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
        return view('admin.qrCode.qrCode');
    }

    public function create(Request $request){

    }


}
