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
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Directory\Counterparty;

class BrandController extends Controller
{

    private string $view = 'brand';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Brand::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::query()->get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = Brand::query()->where('id', '=', $request->id)->first();
        if(!empty($edit->logo)){
            $edit->logo = Storage::url($edit->logo);
        }
        if($edit) {
            return view('admin.directory.'.$this->view.'.edit', compact('edit'));
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
        $editObj->description = $data['description'];

        if($request->file('logo')) {
            if(!empty($editObj->logo)){
                Storage::disk('public')->delete($editObj->logo);
            }
            $fileImage = $request->file('logo');
            $editObj->logo = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-logo', $fileImage, $fileImage->getClientOriginalName(),'public');
        }
        if(!empty($data['delImg']) && $data['delImg'] == 'yes'){
            Storage::disk('public')->delete($editObj->logo);
            $editObj->logo = '';
        }

        $editObj->save();


        $response['url'] = '/admin/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
    {
        $uuidDirectoryFields = $this->objClass::$uuid.'_'.Str::random(30);

        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $editObj = new Brand();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->description = $data['description'];
        $editObj->save();
        if($request->file('logo')) {
            $fileImage = $request->file('logo');
            $editObj->logo = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-logo', $fileImage, $fileImage->getClientOriginalName(),'public');
            $editObj->save();
        }

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
