<?php

namespace App\Http\Controllers\Admin\Page\System;

use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Log\LogRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Document\RecognitionDocument;
use App\Traits\Log\Loggable;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CorrectController extends Controller
{
    use Loggable;

    public function index()
    {
        $documents = RecognitionDocument::query()->where('user_id',1)->get();
        return view('admin.system.correct.index', compact('documents'));
    }


    public function create(Request $request)
    {
        if($request->hasFile('file')){
            $file = $request->file('file');
            $link = Storage::disk('public')->putFileAs('/source/correctTest/'.Carbon::now(), $file, $file->getClientOriginalName(),'public');
            $recognitionDocument = new RecognitionDocument();
            $recognitionDocument->link = $link;
            $recognitionDocument->status = RecognitionDocumentStatusEnum::pending->value;
            $recognitionDocument->user_id = 1;
            $recognitionDocument->file_field = 'test';
            $recognitionDocument->save();
        }
        $response['url'] = '/admin/system/correct';
        $response['status'] = 'success';
        return  $response;
    }

}
