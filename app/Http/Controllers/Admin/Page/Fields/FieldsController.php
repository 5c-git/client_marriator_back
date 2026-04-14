<?php

namespace App\Http\Controllers\Admin\Page\Fields;

use App\Http\Controllers\Controller;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Enum\Fields\FieldsTypeEnum;
use App\Enum\Fields\FieldsDirectoryEnum;
use App\Enum\Fields\PersonalInfoSectionEnum;
use Illuminate\Support\Str;
use App\Models\User\Role;

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
        $typeEnum = FieldsTypeEnum::fieldType();
        $directoryEnum = FieldsDirectoryEnum::cases();
        $sectionEnum = PersonalInfoSectionEnum::cases();
        $fields['fields']['value'] = Fields::where('id', '!=', $request->id)->where('active',true)->get()->toArray();
        $fields['fields']['name'] = 'Простые поля';
        if($field) {
            if(!empty($field->parentFields)) {
                $field->parentFields = json_decode($field->parentFields, true);
                $field->parentFields = array_values($field->parentFields);
            }else{
                $field->parentFields = [];
            }
            foreach (FieldsDirectoryEnum::values() as $directory){
                if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                    $arrData['value'] = $directoryArr;
                    $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                    $fields = array_merge($fields, [$directory=>$arrData]);
                }
            }

            $roles = Role::query()->whereNot('name','admin')->get();

            return view('admin.fields.fieldsEdit', compact('field','typeEnum','directoryEnum','fields','sectionEnum','roles'));
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
        $field->section = $data['section'];
        $field->sort = $data['sort'];
        $field->screen = $data['screen'];
        $field->description = $data['description'];
        $field->step = $data['step'];
        $field->directory = $data['directory'];
        $field->label = $data['label'];
        $field->heading = $data['heading'];
        $field->placeholder = $data['placeholder'];
        $field->drawerInfo_text = $data['drawerInfo_text'];
        $field->default_value = $data['default_value'];

        if(!empty($data['preg_value'])){
            if($this->is_valid_regex($data['preg_value'])) {
                $field->preg_value = $data['preg_value'];
            }else{
                $response['status'] = 'error';
                return response()->json($response);
            }
        }else{
            $field->preg_value = null;
        }
        $field->preg_text = $data['preg_text'];


        $field->helperInfo_text = $data['helperInfo_text'];
        $field->helperInfo_link = $data['helperInfo_link'];
        $field->helperInfo_link_text = $data['helperInfo_link_text'];
        if(!empty($data['helperInfo_link_type'])) {
            $field->helperInfo_link_type = $data['helperInfo_link_type'];
        }


        if(!empty($data['dividerTop'])) {
            $field->dividerTop = true;
        }else{
            $field->dividerTop = false;
        }
        if(!empty($data['dividerBottom'])) {
            $field->dividerBottom = true;
        }else{
            $field->dividerBottom = false;
        }
        if(!empty($data['required'])) {
            $field->required = true;
        }else{
            $field->required = false;
        }
       // 'helperInfo',

        if(empty($data['parentFields'])){
            $data['parentFields'] = [];
        }
        if(!empty($data['parentFields'])) {
            $field->parentFields = json_encode($data['parentFields']);
        }else{
            $field->parentFields = json_encode([]);
        }

        if(!empty($data['active'])) {
            $field->active = true;
        }else{
            $field->active = false;
        }

        if(!empty($data['estate'])) {
            $field->estate = true;
        }else{
            $field->estate = false;
        }
        if(!empty($data['requisites'])) {
            $field->requisites = true;
        }else{
            $field->requisites = false;
        }


        if (empty($data['roles'])) {
            $data['roles'] = [];
        }

        $field->roles()->sync($data['roles']);

        $field->save();


        $response['url'] = '/admin/fields/edit/'.$field->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    private function is_valid_regex(string $pattern): bool {
        @preg_match($pattern, '');
        return preg_last_error() === PREG_NO_ERROR;
    }

    public function fieldsCreate()
    {
        $sectionEnum = PersonalInfoSectionEnum::cases();
        $typeEnum = FieldsTypeEnum::fieldType();
        $directoryEnum = FieldsDirectoryEnum::cases();
        $fields['fields']['value'] = Fields::where('active',true)->get()->toArray();
        $fields['fields']['name'] = 'Простые поля';

        foreach (FieldsDirectoryEnum::values() as $directory){
            if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                $arrData['value'] = $directoryArr;
                $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                $fields = array_merge($fields, [$directory=>$arrData]);
            }
        }

        $roles = Role::query()->whereNot('name','admin')->get();
        $uuidDirectoryFields = Str::random(30);
        return view('admin.fields.fieldsAdd',compact('typeEnum','directoryEnum','fields','uuidDirectoryFields','sectionEnum','roles'));
    }

    public function fieldsCreateAjax(Request $request)
    {
        $data = $request->all();

        $field = new Fields();
        $field->name = $data['name'];
        $field->uuid = $data['uuid'];
        $field->type = $data['type'];
        $field->sort = $data['sort'];
        $field->screen = $data['screen'];
        $field->description = $data['description'];
        $field->step = $data['step'];
        $field->directory = $data['directory'];
        $field->label = $data['label'];
        $field->heading = $data['heading'];
        $field->placeholder = $data['placeholder'];
        $field->drawerInfo_text = $data['drawerInfo_text'];
        $field->default_value = $data['default_value'];
        if(!empty($data['preg_value'])){
            if($this->is_valid_regex($data['preg_value'])) {
                $field->preg_value = $data['preg_value'];
            }else{
                $response['status'] = 'error';
                return response()->json($response);
            }
        }else{
            $field->preg_value = null;
        }
        $field->preg_text = $data['preg_text'];

        $field->helperInfo_text = $data['helperInfo_text'];
        $field->helperInfo_link = $data['helperInfo_link'];
        $field->helperInfo_link_text = $data['helperInfo_link_text'];
        if(!empty($data['helperInfo_link_type'])) {
            $field->helperInfo_link_type = $data['helperInfo_link_type'];
        }
        if(!empty($data['dividerTop'])) {
            $field->dividerTop = true;
        }else{
            $field->dividerTop = false;
        }
        if(!empty($data['dividerBottom'])) {
            $field->dividerBottom = true;
        }else{
            $field->dividerBottom = false;
        }
        if(!empty($data['required'])) {
            $field->required = true;
        }else{
            $field->required = false;
        }

        if(empty($data['parentFields'])){
            $data['parentFields'] = [];
        }
        if(!empty($data['parentFields'])) {
            $field->parentFields = json_encode($data['parentFields']);
        }else{
            $field->parentFields = json_encode([]);
        }
        if(!empty($data['active'])) {
            $field->active = true;
        }else{
            $field->active = false;
        }

        if(!empty($data['estate'])) {
            $field->estate = true;
        }else{
            $field->estate = false;
        }
        if(!empty($data['requisites'])) {
            $field->requisites = true;
        }else{
            $field->requisites = false;
        }

        $field->save();

        if (empty($data['roles'])) {
            $data['roles'] = [];
        }

        $field->roles()->sync($data['roles']);

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
