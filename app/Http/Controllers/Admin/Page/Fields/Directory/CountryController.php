<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Country;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CountryController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function countryList(Request $request)
    {
        $country = Country::get();
        return view('admin.directory.country.country', compact('country'));
    }

    public function countryEdit(Request $request)
    {
        $country = Country::where('id', '=', $request->id)->first();
        if($country) {

            $fields['fields']['value'] = Fields::where('active',true)->get()->toArray();
            $fields['fields']['name'] = 'Простые поля';
            if(!empty($country->parentFields)) {
                $country->parentFields = json_decode($country->parentFields, true);
            }else{
                $country->parentFields = [];
            }
            foreach (FieldsDirectoryEnum::values() as $directory){
                if($directoryArr=$directory::where('active',true)->get()->toArray()) {
                    $arrData['value'] = $directoryArr;
                    $arrData['name'] = FieldsDirectoryEnum::from($directory)->directoryName();
                    $fields = array_merge($fields, [$directory=>$arrData]);
                }
            }

            return view('admin.directory.country.countryEdit', compact('country','fields'));
        }else{
            return redirect()->back();
        }
    }

    public function countryEditAjax(Request $request)
    {

        $country = Country::where('id', '=', $request->id)->first();

        $data = $request->input();

        $country->name = $data['name'];
        $country->uuid = $data['uuid'];
        $country->description = $data['description'];
        if(!empty($data['active'])) {
            $country->active = true;
        }else{
            $country->active = false;
        }
        if(!empty($data['parentFields'])) {
            $country->parentFields = json_encode($data['parentFields']);
        }else{
            $country->parentFields = json_encode([]);
        }

        $country->save();


        $response['url'] = '/admin/directories/directory_country/edit/'.$country->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function countryCreate()
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
        $uuidDirectoryFields = Country::$uuid.'_'.Str::random(30);
        return view('admin.directory.country.countryAdd',compact('uuidDirectoryFields','fields'));
    }

    public function countryCreateAjax(Request $request)
    {
        $data = $request->all();

        $country = new Country();
        $country->name = $data['name'];
        $country->uuid = $data['uuid'];
        $country->description = $data['description'];
        if(!empty($data['active'])) {
            $country->active = true;
        }else{
            $country->active = false;
        }
        if(!empty($data['parentFields'])) {
            $country->parentFields = json_encode($data['parentFields']);
        }else{
            $country->parentFields = json_encode([]);
        }

        $country->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directories/directory_country/edit/' . $country->id;

        return response()->json($response);
    }

    public function countryDelete(Request $request)
    {
        if ($request->id) {
            Country::where('id', '=', $request->id)->delete();
        }
        return redirect()->route('countryList');
    }

}
