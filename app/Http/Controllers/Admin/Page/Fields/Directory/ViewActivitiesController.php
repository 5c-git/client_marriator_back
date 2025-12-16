<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViewActivitiesController extends Controller
{

    private string $view = 'view_activities';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = ViewActivities::class;
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
        $editObj->preview_text = $data['preview_text'];
        $editObj->detail_name = $data['detail_name'];
        $editObj->detail_text = $data['detail_text'];
        $editObj->link_text = $data['link_text'];
        $editObj->external_id = $data['external_id'];
        $editObj->link = $data['link'];
        if(!empty($data['self_employed'])) {
            $editObj->self_employed = true;
        }else{
            $editObj->self_employed = false;
        }
        if(!empty($data['traveling'])) {
            $editObj->traveling = true;
        }else{
            $editObj->traveling = false;
        }
        if(!empty($data['type'])) {
            $editObj->type = $data['type'];
        }
        if(!empty($data['parentFields'])) {
            $editObj->parentFields = json_encode($data['parentFields']);
        }else{
            $editObj->parentFields = json_encode([]);
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
        $editObj->preview_text = $data['preview_text'];
        $editObj->detail_name = $data['detail_name'];
        $editObj->detail_text = $data['detail_text'];
        $editObj->external_id = $data['external_id'];
        $editObj->link_text = $data['link_text'];
        $editObj->link = $data['link'];
        if(!empty($data['self_employed'])) {
            $editObj->self_employed = true;
        }else{
            $editObj->self_employed = false;
        }
        if(!empty($data['traveling'])) {
            $editObj->traveling = true;
        }else{
            $editObj->traveling = false;
        }
        if(!empty($data['type'])) {
            $editObj->type = $data['type'];
        }
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
