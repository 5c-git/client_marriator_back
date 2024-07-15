<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Bank;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        return view('admin.directory.bank.bank', compact('banks'));
    }

    public function bankEdit(Request $request)
    {
        $bank = Bank::where('id', '=', $request->id)->first();
        if($bank) {
            $fields['fields']['value'] = Fields::where('active',true)->get()->toArray();
            $fields['fields']['name'] = 'Простые поля';
            if(!empty($bank->parentFields)) {
                $bank->parentFields = json_decode($bank->parentFields, true);
            }else{
                $bank->parentFields = [];
            }
            foreach (FieldsDirectoryEnum::values() as $directory){
                if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                    $arrData['value'] = $directoryArr;
                    $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                    $fields = array_merge($fields, [$directory=>$arrData]);
                }
            }
            return view('admin.directory.bank.bankEdit', compact('bank','fields'));
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
        if(!empty($data['parentFields'])) {
            $bank->parentFields = json_encode($data['parentFields']);
        }else{
            $bank->parentFields = json_encode([]);
        }

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
        $fields['fields']['value'] = Fields::where('active',true)->get()->toArray();
        $fields['fields']['name'] = 'Простые поля';

        foreach (FieldsDirectoryEnum::values() as $directory){
            if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                $arrData['value'] = $directoryArr;
                $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                $fields = array_merge($fields, [$directory=>$arrData]);
            }
        }
        $uuidDirectoryFields = Bank::$uuid.'_'.Str::random(30);
        return view('admin.directory.bank.bankAdd',compact('uuidDirectoryFields','fields'));
    }

    public function bankCreateAjax(Request $request)
    {
        $data = $request->all();

        $bank = new Bank();
        $bank->name = $data['name'];
        $bank->uuid = $data['uuid'];
        $bank->bic = $data['bic'];
        $bank->description = $data['description'];
        if(!empty($data['parentFields'])) {
            $bank->parentFields = json_encode($data['parentFields']);
        }else{
            $bank->parentFields = json_encode([]);
        }

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
