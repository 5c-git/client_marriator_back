<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DocumentCreator\PdfCreatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Certificates;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
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
        $data = File::get(resource_path('views/document/test.blade.php'));
        return view('admin.documents.test', compact('data'));
    }

    public function save(Request $request){
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, html_entity_decode($request->data));
    }

    public function checkDocument(Request $request){
        $dataOld = File::get(resource_path('views/document/test.blade.php'));
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, html_entity_decode($request->data));
        $user = User::query()->where('id')->first();
        $data = [
            'user'=>$user
        ];
        $dataReturn = (new PdfCreatorService())->generatePdf(
            'document.test',
            $data
        );
        $filePath = resource_path('views/document/test.blade.php');
        File::put($filePath, $dataOld);
        return $dataReturn;
    }

}
