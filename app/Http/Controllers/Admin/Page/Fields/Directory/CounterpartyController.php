<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Height;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Brand;

class CounterpartyController extends Controller
{

    private string $view = 'counterparty';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Counterparty::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::query()->get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = Counterparty::query()->where('id', '=', $request->id)->with(['brands'])->first();
        $brand = Brand::query()->get();
        if($edit) {
            return view('admin.directory.'.$this->view.'.edit', compact('edit','brand'));
        }else{
            return redirect()->back();
        }
    }

    public function editAjax(Request $request)
    {

        $editObj = $this->objClass::query()->where('id', '=', $request->id)->first();

        $data = $request->input();

        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->inn = $data['inn'];
        $editObj->ogrn = $data['ogrn'];
        $editObj->legal_address = $data['legal_address'];
        $editObj->legal_email = $data['legal_email'];

        $brand = [];
        if (!empty($data['brand'])) {
            $brand = array_column($data['brand'], 0);
        }

        $editObj->save();
        $editObj->brands()->sync($brand);

        $response['url'] = '/admin/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
    {
        $uuidDirectoryFields = $this->objClass::$uuid.'_'.Str::random(30);

        $brand = Brand::query()->get();

        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields','brand'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $brand = [];
        if (!empty($data['brand'])) {
            $brand = array_column($data['brand'], 0);
        }

        $editObj = new Counterparty();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->inn = $data['inn'];
        $editObj->ogrn = $data['ogrn'];
        $editObj->legal_address = $data['legal_address'];
        $editObj->legal_email = $data['legal_email'];
        $editObj->save();
        $editObj->brands()->sync($brand);

        $response['status'] = 'success';
        $response['url'] = '/admin/directory_' . $this->view . '/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $this->objClass::query()->where('id', '=', $request->id)->delete();
        }
        return redirect()->route($this->view.'List');
    }

}
