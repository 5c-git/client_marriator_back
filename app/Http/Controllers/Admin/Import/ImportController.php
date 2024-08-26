<?php

namespace App\Http\Controllers\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Enum\Fields\FieldsTypeEnum;
use App\Enum\Fields\FieldsDirectoryEnum;
use App\Enum\Fields\PersonalInfoSectionEnum;
use Illuminate\Support\Str;

class ImportController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index(){
        $directoryEnum = FieldsDirectoryEnum::cases();
        return view('admin.import.import', compact('directoryEnum'));
    }

    public function import(Request $request){
        if(FieldsDirectoryEnum::from($request->importType) && $request->hasFile('importFile')){
            $fileImage = $request->file('importFile');
            $link = Storage::disk('public')->putFileAs('/source/import/'.FieldsDirectoryEnum::from($request->importType)->name, $fileImage, date('d.m.Y:h.i.s').$fileImage->getClientOriginalName(),'public');

            $directoryValue = FieldsDirectoryEnum::from($request->importType)->value::get()->toArray();
            $directoryValueNew = json_decode($fileImage->getContent(),true);
            $dataForImport = [];
            if(!empty($directoryValueNew['items'])){
               $dataForImport = $this->getItemStruct($directoryValueNew['items']);
            }
            $restImportTable = [];
            foreach ($dataForImport as $item){
                $restImportTable[$item['id']]['new'] = $item;
            }
            foreach ($directoryValue as $item){
                $restImportTable[$item['uuid']]['old'] = ['id'=>$item['uuid'],'name'=>$item['name']];
            }
            $response['link'] = Storage::url($link);
            $response['table'] = $restImportTable;

            return response()->json($response, 200);
        }
    }

    public function getItemStruct($data){
        $resData = [];
        if(empty($data["items"]) && !empty($data["id"])){
            $resData[] = $data;
        }else{
            if(!empty($data["items"])){
                foreach ($data["items"] as $item){
                    $resData = array_merge($this->getItemStruct($item["item"]),$resData);
                }
            }else{
                foreach ($data as $item){
                    $resData = array_merge($this->getItemStruct($item["item"]),$resData);
                }
            }
        }
        return $resData;
    }

}
