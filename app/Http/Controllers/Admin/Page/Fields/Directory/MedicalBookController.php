<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\MedicalBook;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalBookController extends Controller
{

    private string $view = 'medical_book';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = MedicalBook::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = $this->objClass::where('id', '=', $request->id)->first();
        if($edit) {

            $fields['fields']['value'] = Fields::where('active',true)->get()->toArray();
            $fields['fields']['name'] = 'Простые поля';
            if(!empty($edit->parentFields)) {
                $edit->parentFields = json_decode($edit->parentFields, true);
            }else{
                $edit->parentFields = [];
            }
            foreach (FieldsDirectoryEnum::values() as $directory){
                if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                    $arrData['value'] = $directoryArr;
                    $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                    $fields = array_merge($fields, [$directory=>$arrData]);
                }
            }

            return view('admin.directory.'.$this->view.'.edit', compact('edit','fields'));
        }else{
            return redirect()->back();
        }
    }

    public function editAjax(Request $request)
    {

        $editObj = $this->objClass::where('id', '=', $request->id)->first();

        $data = $request->input();

        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->sort = $data['sort'];

        if(!empty($data['parentFields'])) {
            $editObj->parentFields = json_encode($data['parentFields']);
        }else{
            $editObj->parentFields = json_encode([]);
        }

        if(!empty($data['active'])) {
            $editObj->active = true;
        }else{
            $editObj->active = false;
        }

        if(!empty($data['default'])) {
            $editObj->default = true;
        }else{
            $editObj->default = false;
        }

        $editObj->save();


        $response['url'] = '/admin/directories/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
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
        $uuidDirectoryFields = $this->objClass::$uuid.'_'.Str::random(30);
        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields','fields'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $editObj = new $this->objClass();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->sort = $data['sort'];

        if(!empty($data['parentFields'])) {
            $editObj->parentFields = json_encode($data['parentFields']);
        }else{
            $editObj->parentFields = json_encode([]);
        }

        if(!empty($data['active'])) {
            $editObj->active = true;
        }else{
            $editObj->active = false;
        }

        if(!empty($data['default'])) {
            $editObj->default = true;
        }else{
            $editObj->default = false;
        }

        $editObj->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directories/directory_'.$this->view.'/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $this->objClass::where('id', '=', $request->id)->delete();
        }
        return redirect()->route($this->view.'List');
    }

}
