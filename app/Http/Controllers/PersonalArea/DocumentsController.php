<?php

namespace App\Http\Controllers\PersonalArea;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use App\Services\OneC\OneCServices;
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
use App\Models\Fields\Directory\Organization;
use App\Models\Certificates;


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
     *           @OA\Examples(example="result", value={"status": "success","result": {{"id": "1","name": "file name"},{"id": "2","name": "file name 2"}}},summary="Успех"),
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

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getDocumentConclude/",
     *     operationId="get document conclude",
     *     tags={"documents"},
     *     summary="Получить компании для заключения договора",
     *     description="Получить компании для заключения договора",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"id": "1","name": "organization name 1"},{"id": "2","name": "organization name 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentConclude(Request $request){
        $user = $request->user();
        $organization = Organization::query()->select(['id','name'])->where('active',true)->get();
        $response = [
            'status' => 'success',
            'result' => ['organization'=>$organization->toArray()]
        ];
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getDocumentTerminate/",
     *     operationId="get document terminate",
     *     tags={"documents"},
     *     summary="Получить компании для расторжения договора",
     *     description="Получить компании для расторжения договора",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"id": "1","name": "organization name 1"},{"id": "2","name": "organization name 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentTerminate(Request $request){
        $user = $request->user();
        $organization = Organization::query()->select(['id','name'])->where('active',true)->get();
        $response = [
            'status' => 'success',
            'result' => ['organization'=>$organization->toArray()]
        ];
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
     *           @OA\Examples(example="result", value={"status": "success","result":{{"id": "1","name": "file name","path": "path"},{"id": "2","name": "file name 2", "path": "path"}}},summary="Успех"),
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
     *     operationId="get Inquiries file data",
     *     tags={"documents"},
     *     summary="Получить документы из раздела Справки",
     *     description="Метод для получениея документов из раздела Справки",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{{"id": "1","name": "file name","path": "path"},{"id": "2","name": "file name 2", "path": "path"}}},summary="Успех"),
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

    /**
     * @OA\Post(
     *     path="/api/personal/documents/setConclude/",
     *     operationId="set сonclude",
     *     tags={"documents"},
     *     summary="Заключить договор с компанией",
     *     description="Метод Заключения договора с компанией",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"ids"},
     *                 @OA\Property(property="ids",type="json"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Запрос получен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function setConclude(Request $request){
        $user = $request->user();
        $response = [
            'status' => 'error',
        ];
        if(!empty($request->ids)){
            if(is_array($request->ids)){
                if((new OneCServices($user))->setConclude($request->ids)->status){
                    $response = [
                        'status' => 'success',
                    ];
                };
            }
        }
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/documents/setTerminate/",
     *     operationId="set terminate",
     *     tags={"documents"},
     *     summary="Расторгнуть договор с компанией",
     *     description="Метод Расторжения договора с компанией",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"ids"},
     *                 @OA\Property(property="ids",type="json"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Запрос получен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function setTerminate(Request $request){
        $user = $request->user();
        $response = [
            'status' => 'error',
        ];
        if(!empty($request->ids)){
            if(is_array($request->ids)){
                if((new OneCServices($user))->setTerminate($request->ids)->status){
                    $response = [
                        'status' => 'success',
                    ];
                };
            }
        }
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/personal/documents/getCompanyAndCertificatesInquiries/",
     *     operationId="get Company And Certificates Inquiries",
     *     tags={"documents"},
     *     summary="Получить компании и типы справок для запроса справки",
     *     description="Метод для получениея компаний и типов справок для запроса справки",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *          @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"id": "1","name": "organization name 1"},{"id": "2","name": "organization name 2"}},"certificates":{{"id": "1","key":"schet","value":"Счет компании"},{"id": "2","key":"schet 2","value":"Счет компании 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getCompanyAndCertificatesInquiries(){
        $organization = Organization::query()->select(['id','name'])->where('active',true)->get();
        $certificates = Certificates::query()->get();
        $response = [
            'status' => 'success',
            'result' => [
                'organization'=>$organization->toArray(),
                'certificates' => $certificates->toArray(),
            ]
        ];
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/personal/documents/requestInquiries/",
     *     operationId="request Inquiries",
     *     tags={"documents"},
     *     summary="Запросить справку",
     *     description="Метод Запроса справки",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"ids","certificates"},
     *                 @OA\Property(property="ids",type="json"),
     *                 @OA\Property(property="certificates",type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Запрос получен",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success"},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function requestInquiries(Request $request){
        $user = $request->user();
        $response = [
            'status' => 'error',
        ];
        if(!empty($request->ids) && !empty($request->certificates)){
            if(is_array($request->ids)){
                $dataInquiries = [
                    'company' => $request->ids,
                    'certificates' => $request->certificates
                ];
                if((new OneCServices($user))->requestInquiries($dataInquiries)->status){
                    $response = [
                        'status' => 'success',
                    ];
                };
            }
        }
        return response()->json($response, 200);
    }

}
