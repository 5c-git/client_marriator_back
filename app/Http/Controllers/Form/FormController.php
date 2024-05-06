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

    public function getForm(Request $request){
        $step = 0;
        if(!empty($request->step)){
            $step = $request->step;
        }
        $formData = [];
        if(!empty($request->formData)){
            $formData = $request->formData;
        }
        echo "<pre>";
        var_dump((new FormBuilderService($step,$formData))->createFormData());
        echo "</pre>";

    }


}
