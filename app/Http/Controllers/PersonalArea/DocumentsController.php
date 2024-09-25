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

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getDocumentSigned/",
     *     operationId="get signet doc",
     *     tags={"documents"},
     *     summary="Получить документы на подписание",
     *     description="Метод для получениея документов необходимых для подписания",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":[{"id": "1","name": "file name"},{"id": "2","name": "file name 2"}]},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentSigned(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Signed)
            ->get();
        $response = [
            'status' => 'success',
            'result' => []
        ];
        foreach ($documents as $document){
            $response['result'][] = [
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
            'result' => []
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

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getDocumentArchive/",
     *     operationId="get archive file data",
     *     tags={"documents"},
     *     summary="Получить документы из архива",
     *     description="Метод для получениея документов из архива",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":[{"id": "1","name": "file name","path": "path"},{"id": "2","name": "file name 2", "path": "path"}]},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentArchive(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Archive)
            ->orderBy('date_signature')
            ->get();
        $response = [
            'status' => 'success',
            'result' => []
        ];
        foreach ($documents as $document){
            $response['result'] = [
                'id' => $document->id,
                'name' => $document->file_name,
                'path' => Storage::disk('private')->temporaryUrl(
                    $document->file_path, now()->addMinutes(30)
                )
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getDocumentInquiries/",
     *     operationId="get archive file data",
     *     tags={"documents"},
     *     summary="Получить документы из раздела Справки",
     *     description="Метод для получениея документов из раздела Справки",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":[{"id": "1","name": "file name","path": "path"},{"id": "2","name": "file name 2", "path": "path"}]},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentInquiries(Request $request){
        $user = $request->user();
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Inquiries)
            ->get();
        $response = [
            'status' => 'success',
            'result' => []
        ];
        foreach ($documents as $document){
            $response['result'] = [
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
