<?php

namespace App\Http\Controllers\Admin\Page\Fields\Directory;

use App\Http\Controllers\Controller;
use App\Models\Fields\Directory\Country;
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
            return view('admin.directory.country.countryEdit', compact('country'));
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

        $country->save();


        $response['url'] = '/admin/directory_country/edit/'.$country->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function countryCreate()
    {
        $uuidDirectoryFields = Country::$uuid.'_'.Str::random(30);
        return view('admin.directory.country.countryAdd',compact('uuidDirectoryFields'));
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

        $country->save();

        $response['status'] = 'success';
        $response['url'] = '/admin/directory_country/edit/' . $country->id;

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
