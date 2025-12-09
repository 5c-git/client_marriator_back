<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Standard;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StandardController extends Controller
{

    private string $view = 'standard';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Standard::class;
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
            return view('admin.directory.'.$this->view.'.edit', compact('edit'));
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
        $editObj->coefficient = $data['coefficient'];

        $editObj->save();

        $response['url'] = '/admin/directories/directory_'.$this->view.'/edit/'.$editObj->id;

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

        $editObj = new $this->objClass();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->coefficient = $data['coefficient'];


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
