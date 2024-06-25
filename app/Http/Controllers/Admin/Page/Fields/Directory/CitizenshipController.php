<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Citizenship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CitizenshipController extends Controller
{

    private string $view = 'citizenship';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Citizenship::class;
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



        if(!empty($data['active'])) {
            $editObj->active = true;
        }else{
            $editObj->active = false;
        }

        $editObj->save();


        $response['url'] = '/admin/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
    {
        $uuidDirectoryFields = $this->objClass::$uuid.Str::random(20);
        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $editObj = new $this->objClass();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];

        if(!empty($data['active'])) {
            $editObj->active = true;
        }else{
            $editObj->active = false;
        }

        $editObj->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directory_'.$this->view.'/edit/' . $editObj->id;

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
