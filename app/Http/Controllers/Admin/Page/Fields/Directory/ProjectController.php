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

class ProjectController extends Controller
{

    private string $view = 'project';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Project::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::query()->get();
        return view('admin.directory.'.$this->view.'.list', compact('list'));
    }

    public function edit(Request $request)
    {
        $edit = Project::query()->where('id', '=', $request->id)->with(['counterparties','places','viewActivities'])->first();
        $place = Place::query()->get();
        $viewActivities = ViewActivities::query()->get();
        $counterparty = Counterparty::query()->get();
        if($edit) {
            return view('admin.directory.'.$this->view.'.edit', compact('edit','place','viewActivities','counterparty'));
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

        $viewActivities = [];
        if (!empty($data['viewActivities'])) {
            foreach ($data['viewActivities'] as $k => $requestViewActivities) {
                $viewActivities[$requestViewActivities[0]] = ['price' => $data['price'][$k]];
            }
        }

        $place = [];
        if (!empty($data['place'])) {
            $place = array_column($data['place'], 0);
        }

        $counterparty = [];
        if (!empty($data['counterparty'])) {
            $counterparty = array_column($data['counterparty'], 0);
        }

        $editObj->save();
        $editObj->viewActivities()->sync($viewActivities);
        $editObj->places()->sync($place);
        $editObj->counterparties()->sync($counterparty);


        $response['url'] = '/admin/directory_'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create()
    {
        $uuidDirectoryFields = $this->objClass::$uuid.'_'.Str::random(30);

        $place = Place::query()->get();
        $viewActivities = ViewActivities::query()->get();
        $counterparty = Counterparty::query()->get();

        return view('admin.directory.'.$this->view.'.add', compact('uuidDirectoryFields','place','viewActivities','counterparty'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $viewActivities = [];
        if (!empty($data['viewActivities'])) {
            foreach ($data['viewActivities'] as $k => $requestViewActivities) {
                $viewActivities[$requestViewActivities[0]] = ['price' => $data['price'][$k]];
            }
        }

        $place = [];
        if (!empty($data['place'])) {
            $place = array_column($data['place'], 0);
        }

        $counterparty = [];
        if (!empty($data['counterparty'])) {
            $counterparty = array_column($data['counterparty'], 0);
        }

        $editObj = new Project();
        $editObj->name = $data['name'];
        $editObj->uuid = $data['uuid'];
        $editObj->save();
        $editObj->viewActivities()->sync($viewActivities);
        $editObj->places()->sync($place);
        $editObj->counterparties()->sync($counterparty);


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
