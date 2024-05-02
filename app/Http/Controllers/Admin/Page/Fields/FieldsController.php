<?php

namespace App\Http\Controllers\Admin\Page\Fields;

use App\Http\Controllers\Controller;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Enum\Fields\FieldsTypeEnum;
use App\Enum\Fields\FieldsDirectoryEnum;

class FieldsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function fieldsList(Request $request)
    {
        $fields = Fields::get();
        return view('admin.fields.fields', compact('fields'));
    }

    public function fieldsEdit(Request $request)
    {
        $field = Fields::where('id', '=', $request->id)->first();
        $typeEnum = FieldsTypeEnum::cases();
        $directoryEnum = FieldsDirectoryEnum::cases();
        $fields = Fields::where('id', '!=', $request->id)->get();

        if($field) {
            if(!empty($field->parentFields)) {
                $field->parentFields = json_decode($field->parentFields, true);
            }else{
                $field->parentFields = [];
            }
            return view('admin.fields.fieldsEdit', compact('field','typeEnum','directoryEnum','fields'));
        }else{
            return redirect()->back();
        }

    }

    public function fieldsEditAjax(Request $request)
    {

        $field = Fields::where('id', '=', $request->id)->first();

        $data = $request->input();

        $field->name = $data['name'];
        $field->uuid = $data['uuid'];
        $field->type = $data['type'];
        $field->description = $data['description'];
        if(empty($data['parentFields'])){
            $data['parentFields'] = [];
        }
        $field->parentFields = json_encode($data['parentFields']);

        if(!empty($data['active'])) {
            $field->active = true;
        }else{
            $field->active = false;
        }

        $field->save();


        $response['url'] = '/admin/fields/edit/'.$field->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function fieldsCreate()
    {
        $typeEnum = FieldsTypeEnum::cases();
        $directoryEnum = FieldsDirectoryEnum::cases();
        $fields = Fields::get();
        return view('admin.fields.fieldsAdd',compact('typeEnum','directoryEnum','fields'));
    }

    public function fieldsCreateAjax(Request $request)
    {
        $data = $request->all();

        $field = new Fields();
        $field->name = $data['name'];
        $field->uuid = $data['uuid'];
        $field->type = $data['type'];
        $field->description = $data['description'];
        if(empty($data['parentFields'])){
            $data['parentFields'] = [];
        }
        $field->parentFields = json_encode($data['parentFields']);
        if(!empty($data['active'])) {
            $field->active = true;
        }else{
            $field->active = false;
        }

        $field->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/fields/edit/' . $field->id;

        return response()->json($response);
    }

    public function fieldsDelete(Request $request)
    {
        if ($request->id) {
            Fields::where('id', '=', $request->id)->delete();
        }
        return redirect()->route('fieldsList');
    }

}
