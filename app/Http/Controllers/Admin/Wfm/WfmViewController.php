<?php

namespace App\Http\Controllers\Admin\Wfm;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Enum\Wfm\WfmTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Height;
use App\Models\Fields\Fields;
use App\Models\Wfm\WfmViewActivities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WfmViewController extends Controller
{

    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = WfmViewActivities::class;
    }

    public function list(Request $request)
    {
        $type = $request->wfmType;
        $enum = WfmTypeEnum::fromName($type);
        $list = $this->objClass::where('type',$enum->value)->get();
        return view('admin.wfm.view_activities.list', compact('list','enum'));
    }

    public function edit(Request $request)
    {
        $edit = $this->objClass::where('id', '=', $request->id)->first();
        if($edit) {
            $type = $request->wfmType;
            $enum = WfmTypeEnum::fromName($type);
            return view('admin.wfm.view_activities.edit', compact('edit','enum'));
        }else{
            return redirect()->back();
        }
    }

    public function editAjax(Request $request)
    {

        $editObj = $this->objClass::where('id', '=', $request->id)->first();

        $data = $request->input();

        $editObj->externalId = $data['externalId'];
        $editObj->name = $data['name'];

        $editObj->save();

        $response['url'] = '/admin/wfm/view_activities/'.$editObj->type->name.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create(Request $request)
    {
        $type = $request->wfmType;
        $enum = WfmTypeEnum::fromName($type);
        return view('admin.wfm.view_activities.add', compact('enum'));
    }

    public function createAjax(Request $request)
    {
        $type = $request->wfmType;
        $editObj = new $this->objClass();
        $data = $request->input();
        $editObj->type = WfmTypeEnum::fromName($type)->value;
        $editObj->externalId = $data['externalId'];
        $editObj->name = $data['name'];

        $editObj->save();


        $editObj->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/wfm/view_activities/'.$editObj->type->name.'/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function delete(Request $request)
    {
        $type = $request->wfmType;
        $enum = WfmTypeEnum::fromName($type);
        if ($request->id) {
            $this->objClass::where('id', '=', $request->id)->delete();
        }
        return redirect()->route('wfmViewList',['wfmType'=>$enum->name]);
    }

}
