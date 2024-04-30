<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function bankList(Request $request)
    {
        $banks = Bank::get();
        return view('admin.bank.bank', compact('banks'));
    }

    public function bankEdit(Request $request)
    {
        $bank = Bank::where('id', '=', $request->id)->first();
        if($bank) {
            return view('admin.bank.bankEdit', compact('bank'));
        }else{
            return redirect()->back();
        }
    }

    public function bankEditAjax(Request $request)
    {

        $bank = Bank::where('id', '=', $request->id)->first();

        $data = $request->input();

        $bank->name = $data['name'];
        $bank->uuid = $data['uuid'];
        $bank->bic = $data['bic'];
        $bank->description = $data['description'];
        if(!empty($data['active'])) {
            $bank->active = true;
        }else{
            $bank->active = false;
        }

        $bank->save();


        $response['url'] = '/admin/directory_bank/edit/'.$bank->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function bankCreate()
    {
        return view('admin.bank.bankAdd');
    }

    public function bankCreateAjax(Request $request)
    {
        $data = $request->all();

        $bank = new Bank();
        $bank->name = $data['name'];
        $bank->uuid = $data['uuid'];
        $bank->bic = $data['bic'];
        $bank->description = $data['description'];
        if(!empty($data['active'])) {
            $bank->active = true;
        }else{
            $bank->active = false;
        }

        $bank->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directory_bank/edit/' . $bank->id;

        return response()->json($response);
    }

    public function bankDelete(Request $request)
    {
        if ($request->id) {
            Bank::where('id', '=', $request->id)->delete();
        }
        return redirect()->route('bankList');
    }

}
