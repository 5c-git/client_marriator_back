<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Models\Document\Document;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class DocumentsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getDocumentSigned(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Signed)
            ->get();
        $response = [
            'status' => 'success',
            'document' => []
        ];
        foreach ($documents as $document){
            $response['document'] = [
                'id' => $document->id,
                'name' => $document->file_name,
            ];
        }
        return response()->json($response, 200);
    }
    public function getDocumentConclude(Request $request){
        $user = $request->user();

        $response = [
            'status' => 'success',
            'document' => []
        ];

        //??????????????????????
        return response()->json($response, 200);
    }
    public function getDocumentTerminate(Request $request){
        $user = $request->user();
        $response = [
            'status' => 'success',
            'document' => []
        ];

        //??????????????????????
        return response()->json($response, 200);
    }
    public function getDocumentArchive(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Archive)
            ->orderBy('date_signature')
            ->get();
        $response = [
            'status' => 'success',
            'document' => []
        ];
        foreach ($documents as $document){
            $response['document'] = [
                'id' => $document->id,
                'name' => $document->file_name,
                'path' => Storage::disk('private')->temporaryUrl(
                    $document->file_path, now()->addMinutes(30)
                )
            ];
        }
        return response()->json($response, 200);
    }
    public function getDocumentInquiries(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Inquiries)
            ->get();
        $response = [
            'status' => 'success',
            'document' => []
        ];
        foreach ($documents as $document){
            $response['document'] = [
                'id' => $document->id,
                'name' => $document->file_name,
                'path' => Storage::disk('private')->temporaryUrl(
                    $document->file_path, now()->addMinutes(30)
                )
            ];
        }
        return response()->json($response, 200);
    }

    public function setConclude(Request $request){
        if(!empty($request->ids)){

        }
    }

    public function setTerminate(Request $request){
        if(!empty($request->ids)){

        }
    }

}
