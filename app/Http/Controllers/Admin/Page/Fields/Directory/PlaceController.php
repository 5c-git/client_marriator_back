<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Height;
use App\Models\Fields\Fields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\RegionOfResidence;

class PlaceController extends Controller
{

    private string $view = 'place';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Place::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::query()->get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = Place::query()->where('id', '=', $request->id)->first();
        $regions = RegionOfResidence::get();
        if($edit) {
            return view('admin.directory.'.$this->view.'.edit', compact('edit','regions'));
        }else{
            return redirect()->back();
        }
    }

    public function editAjax(Request $request)
    {

        $editObj = $this->objClass::query()->where('id', '=', $request->id)->first();

        $data = $request->input();

        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->address_kladr = $data['address_kladr'];
        $editObj->latitude = $data['latitude'];
        $editObj->longitude = $data['longitude'];
        $editObj->directory_region_of_residence_id = $data['region'];

        $editObj->save();

        $response['url'] = '/admin/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
    {
        $uuidDirectoryFields = $this->objClass::$uuid.'_'.Str::random(30);
        $regions = RegionOfResidence::get();
        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields','regions'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $editObj = new Place();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->address_kladr = $data['address_kladr'];
        $editObj->latitude = $data['latitude'];
        $editObj->longitude = $data['longitude'];
        $editObj->directory_region_of_residence_id = $data['region'];
        $editObj->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directory_' . $this->view . '/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $this->objClass::query()->where('id', '=', $request->id)->delete();
        }
        return redirect()->route($this->view.'List');
    }

}
