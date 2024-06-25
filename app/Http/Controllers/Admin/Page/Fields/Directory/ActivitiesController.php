<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Activities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivitiesController extends Controller
{

    private string $view = 'activities';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Activities::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = $this->objClass::where('id', '=', $request->id)->first();
        if(!empty($edit->img)){
            $edit->img = Storage::url($edit->img);
        }
        if(!empty($edit->detail_img)){
            $edit->detail_img = Storage::url($edit->detail_img);
        }
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
        $editObj->preview_text = $data['preview_text'];
        $editObj->detail_name = $data['detail_name'];
        $editObj->detail_text = $data['detail_text'];
        $editObj->link_text = $data['link_text'];
        $editObj->link = $data['link'];
        if(!empty($data['type'])) {
            $editObj->type = $data['type'];
        }

        if($request->file('img')) {
            if(!empty($editObj->img)){
                Storage::disk('public')->delete($editObj->img);
            }
            $fileImage = $request->file('img');
            $editObj->img = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-img', $fileImage, $fileImage->getClientOriginalName(),'public');
        }
        if(!empty($data['delImg']) && $data['delImg'] == 'yes'){
            Storage::disk('public')->delete($editObj->img);
            $editObj->img = '';
        }

        if($request->file('detail_img')) {
            if(!empty($editObj->detail_img)){
                Storage::disk('public')->delete($editObj->detail_img);
            }
            $fileImage = $request->file('detail_img');
            $editObj->detail_img = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-imgDetail', $fileImage, $fileImage->getClientOriginalName(),'public');
        }
        if(!empty($data['delImgDetail']) && $data['delImgDetail'] == 'yes'){
            Storage::disk('public')->delete($editObj->detail_img);
            $editObj->detail_img = '';
        }

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
        $editObj->preview_text = $data['preview_text'];
        $editObj->detail_name = $data['detail_name'];
        $editObj->detail_text = $data['detail_text'];
        $editObj->link_text = $data['link_text'];
        $editObj->link = $data['link'];
        if(!empty($data['type'])) {
            $editObj->type = $data['type'];
        }

        if(!empty($data['active'])) {
            $editObj->active = true;
        }else{
            $editObj->active = false;
        }
        $editObj->save();

        if($request->file('detail_img')) {
            $fileImage = $request->file('detail_img');
            $editObj->detail_img = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-imgDetail', $fileImage, $fileImage->getClientOriginalName(),'public');
            $editObj->save();
        }
        if($request->file('img')) {
            $fileImage = $request->file('img');
            $editObj->img = Storage::disk('public')->putFileAs('/source/directory/'.$this->view.'/'.$editObj->id.'-img', $fileImage, $fileImage->getClientOriginalName(),'public');
            $editObj->save();
        }

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
