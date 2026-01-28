<?php

namespace App\Http\Controllers\PersonalArea;

use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Document\DocumentResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Document\RecognitionDocument;
use App\Models\Fields\Directory\Counterparty;
use App\Models\User;
use App\Models\User\UserContractData;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use App\Services\Nopaper\NopaperService;
use App\Services\OneC\OneCServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Document\Document;
use App\Enum\Document\DocumentStatusEnum;
use App\Models\Fields\Directory\Organization;
use App\Models\Certificates;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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
     *           @OA\Examples(example="result", value={"status": "success","result": {{"uuid": "1","name": "file name"},{"uuid": "2","name": "file name 2"}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentSigned(Request $request){
        $user = $request->user();

        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Signed->value)
            ->where('status_signature','!=',DocumentStatusSignatureEnum::Signed->value)
            ->orderBy('id','desc')
            ->get();

        return DocumentResource::collection($documents);
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
     *           @OA\Examples(example="result", value={"status": "success","result":{{"uuid": "1","name": "file name","path": "path"},{"uuid": "2","name": "file name 2", "path": "path"}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentArchive(Request $request){
        $user = $request->user();

        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Signed->value)
            ->where('status_signature',DocumentStatusSignatureEnum::Signed->value)
            ->orderBy('id','desc')
            ->get();

        return DocumentResource::collection($documents);
    }

    public function createDocument(Request $request)
    {
//        $user = $request->user();
//
//        $sourcePath = public_path('nameTest.pdf');
//        $destinationPath = "source/document/".$user->id."/".date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $fileContent = File::get($sourcePath);
//        Storage::disk('public')->put($destinationPath, $fileContent);
//        $fileUrl = Storage::url($destinationPath);
//        $doc = new Document();
//        $doc->uuid = Str::uuid();
//        $doc->user_id = $user->id;
//        $doc->file_name = date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $doc->file_path = $destinationPath;
//        $doc->status = DocumentStatusEnum::Signed->value;
//        $doc->status_signature = DocumentStatusSignatureEnum::NoSend->value;
//        $doc->date_signature = Carbon::now();
//        $doc->save();
//
//        $sourcePath = public_path('nameTest.pdf');
//        $destinationPath = "source/document/".$user->id."/".date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $fileContent = File::get($sourcePath);
//        Storage::disk('public')->put($destinationPath, $fileContent);
//        $fileUrl = Storage::url($destinationPath);
//        $doc = new Document();
//        $doc->uuid = Str::uuid();
//        $doc->user_id = $user->id;
//        $doc->file_name = date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $doc->file_path = $destinationPath;
//        $doc->status = DocumentStatusEnum::Signed->value;
//        $doc->status_signature = DocumentStatusSignatureEnum::NoSend->value;
//        $doc->date_signature = Carbon::now();
//        $doc->save();
//
//
        return new SuccessResource();
    }



    public function signedDocument(Request $request){
        $user = $request->user();
        /** @var User $user */
        if(!empty($user->nopaper_guid) && !empty($user->nopaper_certificate_id)) {
            //(new NopaperService())
            return new SuccessResource();
        }else{
            return new ErrorResource();
        }
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
     *           @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"uuid": "1","name": "organization name 1"},{"uuid": "2","name": "organization name 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentConclude(Request $request){
        $user = $request->user();
        $userData = UserContractData::query()
            ->where('user_id',$user->id)
            ->where('date_start','<=',Carbon::now())
            ->where('date_end','>=',Carbon::now())
            ->get();
        $counterpartiesIds = [];
        foreach ($userData as $user){
            $counterpartiesIds[] = $user->counterparty_id;
        }
        $organization = Organization::query()->select(['uuid','name'])->where('active',true)
            ->whereNotIn('counterparty_id',$counterpartiesIds)
            ->get();
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
     *           @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"uuid": "1","name": "organization name 1"},{"uuid": "2","name": "organization name 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentTerminate(Request $request){
        $user = $request->user();
        $organization = Organization::query()->select(['uuid','name'])->where('active',true)->get();
        $response = [
            'status' => 'success',
            'result' => ['organization'=>$organization->toArray()]
        ];
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
     *           @OA\Examples(example="result", value={"status": "success","result":{{"uuid": "1","name": "file name","path": "path"},{"uuid": "2","name": "file name 2", "path": "path"}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getDocumentInquiries(Request $request){
        $user = $request->user();
        $this->createMockData($user->id,DocumentStatusEnum::Inquiries->value);
        $documents = Document::query()
            ->where('user_id',$user->id)
            ->where('status',DocumentStatusEnum::Inquiries->value)
            ->get();
        $response = [
            'status' => 'success',
            'result' => []
        ];
        foreach ($documents as $k=>$document){
            $response['result'][$k] = [
                'uuid' => $document->uuid,
                'name' => $document->file_name,
                'path' => $document->file_path,
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
     *                 required={"uuid"},
     *                 @OA\Property(property="uuid",type="array",@OA\Items(type="string")),
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
//        $user = $request->user();
//        $response = [
//            'status' => 'error',
//        ];
//        if(!empty($request->uuid)){
//            if(is_array($request->uuid)){
//                if((new OneCServices($user))->setConclude($request->uuid)->status){
//                    $response = [
//                        'status' => 'success',
//                    ];
//                };
//            }
//        }
//        return response()->json($response, 200);
        $response = [
            'status' => 'error',
        ];
        $user = $request->user();
        if(is_array($request->uuid)) {
            $organizations = Organization::query()->whereIn('uuid', $request->uuid)->get();
        }else{
            $organizations = Organization::query()->where('uuid', $request->uuid)->get();
        }
        $counterpartyIds = [];
        foreach ($organizations as $organization){
            $counterpartyIds[] = $organization->counterparty_id;
        }
        $counterparties = Counterparty::query()
            ->whereIn('id',$counterpartyIds)
            ->get();
        $documents = collect();
        $service = new UserDocumentCreatorService();
        foreach ($counterparties as $counterparty){
            $document = $service->createContract($user,$counterparty);
            if($document){
                $documents->push($document);
                $response = [
                        'status' => 'success',
                ];
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
     *                 required={"uuid"},
     *                 @OA\Property(property="uuid",type="array",@OA\Items(type="string")),
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
        if(!empty($request->uuid)){
            if(is_array($request->uuid)){
                if((new OneCServices($user))->setTerminate($request->uuid)->status){
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
     *          @OA\Examples(example="result", value={"status": "success","result": {"organization":{{"uuid": "1","name": "organization name 1"},{"uuid": "2","name": "organization name 2"}},"certificates":{{"id": "1","key":"schet","value":"Счет компании"},{"id": "2","key":"schet 2","value":"Счет компании 2"}}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getCompanyAndCertificatesInquiries(){
        $organization = Organization::query()->select(['uuid','name'])->where('active',true)->get();
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
     *                 required={"uuid","certificates"},
     *                 @OA\Property(property="uuid",type="string"),
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

    public function requestInquiries(Request $request)
    {
        $user = $request->user();
        $response = [
            'status' => 'error',
        ];
        if (!empty($request->uuid) && !empty($request->certificates)) {

            $dataInquiries = [
                'company' => $request->uuid,
                'certificates' => $request->certificates
            ];
            if ((new OneCServices($user))->requestInquiries($dataInquiries)->status) {
                $response = [
                    'status' => 'success',
                ];
            };

        }
        return response()->json($response, 200);
    }

    public function createMockData($userId,$status){
        foreach ([
            'testLog1'=>['name'=>'testLog1.pdf','path'=>'/testDir/file/file.pdf'],
            'testLog2'=>['name'=>'testLog2.pdf','path'=>'/testDir/file/file.pdf'],
            'testLog3'=>['name'=>'testLog3.pdf','path'=>'/testDir/file/file.pdf'],
                 ] as $k=>$uuid) {
            Document::query()->updateOrCreate([
                'user_id' => $userId,
                'status' => $status,
                'uuid' => $k,
            ], [
                'user_id' => $userId,
                'status' => $status,
                'uuid' => $k,
                'file_path'=>$uuid['path'],
                'file_name'=>$uuid['name'],
                'status_signature'=>DocumentStatusSignatureEnum::NoSend->value,
                'date_signature'=>Carbon::now()
            ]);
        }
    }

}
