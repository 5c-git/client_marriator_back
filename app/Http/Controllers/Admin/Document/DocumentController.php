<?php

namespace App\Http\Controllers\Admin\Document;

use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use App\Enum\Document\DocumentTemplates\DocumentTemplatesFieldEnum;
use App\Enum\Fields\FieldsDirectoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Document\DocumentTemplate;
use App\Models\Document\RecognitionDocument;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\HairColor;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\DocumentCreator\PdfCreatorService;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Certificates;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentController extends Controller
{

    private string $view = 'documents';
    public string $error = '';
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
        $case = DocumentTemplatesFieldEnum::cases();
        return view('admin.documents.test', compact('data','case'));
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
            $data = File::get(resource_path('views/document/'.$edit->type->name.$edit->date_start .$edit->date_end.'.blade.php'));
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
        $editObj->date_end = $data['date_end'];
        $editObj->date_start = $data['date_start'];
        $editObj->type = $data['type'];
        $editObj->template = $data['template'];
        $editObj->number = $data['number']??null;
        $editObj->place = $data['place']??null;

        $this->checkData($editObj);
        $this->checkSpace($editObj);
        $this->checkSplit($editObj);
        $this->checkActive($editObj);

        if($this->error){
            $response['status'] = 'error';
            $response['message'] = $this->error;
            return response()->json($response);
        }

        $editObj->save();

        $filename = $editObj->type->name . $editObj->date_start .$editObj->date_end . '.blade.php';
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
        $editObj->date_end = $data['date_end'];
        $editObj->date_start = $data['date_start'];
        $editObj->type = $data['type'];
        $editObj->template = $data['template'];
        $editObj->number = $data['number']??null;
        $editObj->place = $data['place']??null;

        $this->checkData($editObj);
        $this->checkSpace($editObj);
        $this->checkSplit($editObj);
        $this->checkActive($editObj);

        if($this->error){
            $response['status'] = 'error';
            $response['message'] = $this->error;
            return response()->json($response);
        }

        $editObj->save();
        $editObj = $this->objClass::where('id', '=', $editObj->id)->first();
        $filename = $editObj->type->name . $editObj->date_start .$editObj->date_end . '.blade.php';
        $path = resource_path('views/document/' . $filename);
        $content = $data['content'];
        File::ensureDirectoryExists(dirname($path));
        File::put($path, html_entity_decode($this->getPreData().$content));

        $response['status'] = 'success';
        $response['url'] = '/admin/'.$this->view.'/edit/' . $editObj->id;

        return response()->json($response);
    }

    public function checkData(DocumentTemplate $documentTemplate)
    {
        if($this->error){
            return;
        }
        if($documentTemplate->date_start >= $documentTemplate->date_end){
            $this->error = 'Дата конца не должна быть меньше чем дата старта';
        }
    }

    public function checkSpace(DocumentTemplate $documentTemplate)
    {
        if($this->error){
            return;
        }
        $date = $documentTemplate->date_start->clone();
        if (
            DocumentTemplate::query()
                ->where('type', $documentTemplate->type->value)
                ->exists() &&
            (!DocumentTemplate::query()
                ->where('type', $documentTemplate->type->value)
                ->where('date_end', $date->subDay())
                ->exists()
                &&
                DocumentTemplate::query()
                    ->where('type', $documentTemplate->type->value)
                    ->where('date_end','<=', $date->subDay())
                    ->exists()
            )
        )
        {
            $this->error = 'Интервал времени между документами должен отсутствовать';
        }
    }
    public function checkSplit(DocumentTemplate $documentTemplate){
        if($this->error){
            return;
        }
        if (
            DocumentTemplate::query()
                ->where('id','!=',$documentTemplate->id)
                ->where('type', $documentTemplate->type->value)
                ->where('date_start','<=', $documentTemplate->date_end)
                ->where('date_end','>=', $documentTemplate->date_start)
                ->exists()
        )
        {
            $this->error = 'Интервал времени уже используется';
        }
    }

    public function checkActive(DocumentTemplate $documentTemplate){
        if($this->error){
            return;
        }
        $doc = DocumentTemplate::query()
            ->where('type', $documentTemplate->type->value)
            ->where('date_start','<=', Carbon::now())
            ->where('date_end','>=', Carbon::now())
            ->first();
        if (!empty($doc))
        {
            if(
                $doc->id !== $documentTemplate->id &&
                !$doc->date_end->lt($documentTemplate->date_start)
            )
            $this->error = 'Нельзя редактировать и создавать прошедшие документы';
        }
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
        $service = new UserDocumentCreatorService();
        $counterparty = Counterparty::query()->first();
        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        [$dataContract,$dataForSave] = $service->getDataForContract($user, $counterparty);
        return $dataContract;
    }

}
