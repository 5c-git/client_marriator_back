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

            foreach ($directoryValue as $item){
                $restImportTable[$item['uuid']]['old'] = ['id'=>$item['uuid'],'name'=>$item['name']];
            }
            $count = count($restImportTable);
            foreach ($dataForImport as $item){
                if(
                    empty($restImportTable[$item['id']]) || $count<1000
//                      ||
//                    !empty($restImportTable[$item['id']]['old']) && (
//                        (!empty($item['name']) && $restImportTable[$item['id']]['old']['name'] != $item['name']) ||
//                        (!empty($item['code']) && $restImportTable[$item['id']]['old']['name'] != $item['code'])
//                    )
                ){
                    $restImportTable[$item['id']]['new'] = $item;
                }else {
                    unset($restImportTable[$item['id']]);
                }
            }

            $response['link'] = $link;
            $response['type'] = $request->importType;
            $response['table'] = $restImportTable;
            $response['status'] = 'success';

            return response()->json($response, 200);
        }else{
            return response()->json(['status'=>'error'], 200);
        }
    }

    public function importSave(Request $request){
        if(!empty($request->link) && FieldsDirectoryEnum::from($request->type)){
            $fileImport = Storage::disk('public')->get($request->link);
            if(!empty($fileImport)) {
                $directoryValueNew = json_decode($fileImport, true);
                $dataForImport = [];
                if (!empty($directoryValueNew['items'])) {
                    $dataForImport = $this->getItemStruct($directoryValueNew['items']);
                    if(!empty($dataForImport)){
                        $directory = FieldsDirectoryEnum::from($request->type)->value;
                        $directory::upsertFromImport($dataForImport);
                        return response()->json(['status'=>'success'], 200);
                    }
                }
            }
        }
        return response()->json(['status'=>'error'], 200);
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
