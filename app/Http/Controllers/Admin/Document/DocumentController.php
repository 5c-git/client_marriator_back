<?php

namespace App\Http\Controllers\Admin\Document;

use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Document\DocumentTemplate;
use App\Models\Fields\Directory\HairColor;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\DocumentCreator\PdfCreatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Certificates;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentController extends Controller
{

    private string $view = 'documents';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = DocumentTemplate::class;
    }

    public function index(Request $request){
        $data = File::get(resource_path('views/document/test.blade.php'));
        return view('admin.documents.test', compact('data'));
    }

    public function save(Request $request){
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, html_entity_decode($this->getPreData().$request->data));
    }

    public function checkDocument(Request $request){

        $dataOld = File::get(resource_path('views/document/test.blade.php'));
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, html_entity_decode($this->getPreData().$request->data));
        $data = $this->getTestData();
        $dataReturn = (new PdfCreatorService())->generatePdf(
            'document.test',
            $data
        );
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, $dataOld);
        return $dataReturn;
    }

    private function getPreData()
    {
        return "<style>body {font-family: 'DejaVu Sans', sans-serif;}</style>";
    }

    public function list(Request $request)
    {
        $docEnum = constant("App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum::$request->templateType");
        $list = $this->objClass::where('type',$docEnum->value)->get();
        return view('admin.'.$this->view.'.'.$request->templateType.'.list', compact('list','docEnum'));
    }

    public function edit(Request $request)
    {
        $edit = $this->objClass::where('id', '=', $request->id)->first();
        if($edit) {
            $data = File::get(resource_path('views/document/'.$edit->type->name.$edit->version.'.blade.php'));
            return view('admin.'.$this->view.'.'.$edit->type->name.'.edit', compact('edit','data'));
        }else{
            return redirect()->back();
        }
    }

    public function editAjax(Request $request)
    {

        $editObj = $this->objClass::where('id', '=', $request->id)->first();

        $data = $request->input();

        $editObj->name = $data['name'];
        $editObj->version = $data['version'];
        $editObj->type = $data['type'];
        $editObj->template = $data['template'];
        $editObj->save();

        $filename = $editObj->type->name . $editObj->version . '.blade.php';
        $path = resource_path('views/document/' . $filename);
        $content = $data['content'];
        File::ensureDirectoryExists(dirname($path));
        File::put($path, html_entity_decode($this->getPreData().$content));

        $response['url'] = '/admin/'.$this->view.'/edit/'.$editObj->id;

        $response['status'] = 'success';

        return response()->json($response);

    }

    public function create(Request $request)
    {
        $docEnum = constant("App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum::$request->templateType");
        return view('admin.'.$this->view.'.'.$request->templateType.'.add', compact('docEnum'));
    }

    public function createAjax(Request $request)
    {
        $data = $request->all();

        $editObj = new $this->objClass();
        $editObj->name = $data['name'];
        $editObj->version = $data['version'];
        $editObj->type = $data['type'];
        $editObj->template = $data['template'];

        $editObj->save();
        $editObj = $this->objClass::where('id', '=', $editObj->id)->first();
        $filename = $editObj->type->name . $editObj->version . '.blade.php';
        $path = resource_path('views/document/' . $filename);
        $content = $data['content'];
        File::ensureDirectoryExists(dirname($path));
        File::put($path, html_entity_decode($this->getPreData().$content));

        $response['status'] = 'success';
        $response['url'] = '/admin/'.$this->view.'/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $editObj = $this->objClass::where('id', '=', $request->id)->first();
            if($editObj) {
                $this->objClass::where('id', '=', $request->id)->delete();
                return redirect()->route($this->view.'List',$editObj->type->name);
            }
        }
        return redirect()->route($this->view.'List',DocumentTemplatesEnum::payment->name);
    }

    public function getTestData(): array
    {
        return [
            'name'        => 'ТестИмени',
            'lastName'    => 'ТестФамилии',
            'secondName'  => 'Тест отчества',
            'totalAmount' => '00.00',
        ];
    }

}
