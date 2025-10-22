<?php

namespace App\Services\Nopaper;

use App\Enum\Document\DocumentFieldTypeEnum;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Document\DocumentTypeEnum;
use App\Models\Document\Document;
use App\Models\Document\RecognitionDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NopaperService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.nopaper.base_url');
        $this->apiKey = config('services.nopaper.api_key');
    }

    public function sendDocumentsToNopaper(User $user): bool
    {
        if($this->checkUserExists($user)) {
            $draftData = [
                'recipientInfoList' => [
                    [
                        "userPhone"  => $user->phone,
                        "actionType" => 1,
                        "SignType"   => 1,
                    ]
                ],
            ];

            $draftResponse = $this->createDraft($draftData);
            if(!empty($draftResponse['documentId'])) {
                $files = Document::query()
                    ->where('user_id',$user->id)
                    ->where('status',DocumentStatusEnum::Signed->value)
                    ->where('status_signature',DocumentStatusSignatureEnum::NoSend->value)
                    ->get();

                foreach ($files as $file) {
                    $imagePath = str_replace('/storage','',$file->file_path);
                    $fileContent = base64_encode(Storage::disk('public')->get($imagePath));
                    $fileData    = [
                        'fileInfo' => [
                            'fileNameWithExtension' => basename($file->file_path),
                            'filebase64'            => $fileContent
                        ]
                    ];
                    $fileDataResponse = $this->attachFileToDocument($draftResponse['documentId'], $fileData);
                    if(!empty($fileDataResponse['fileId'])) {
                        $file->file_id = $fileDataResponse['fileId'];
                        $file->save();
                    }
                }

                if($this->sendDocument($draftResponse['documentId'])){
                    Document::query()
                        ->where('user_id', $user->id)
                        ->where('status', DocumentStatusEnum::Signed->value)
                        ->where('status_signature', DocumentStatusSignatureEnum::NoSend->value)
                        ->update([
                            'status_signature' => DocumentStatusSignatureEnum::Process->value,
                            'document_id' => $draftResponse['documentId']
                        ]);
                    $this->sendSms($user, $draftResponse['documentId']);
                    return true;
                }
            }
        }
        return false;
    }

    public function confirmSms(User $user, $code): array
    {
        $document = Document::query()
            ->where('user_id', $user->id)
            ->where('status', DocumentStatusEnum::Signed->value)
            ->where('status_signature', DocumentStatusSignatureEnum::Process->value)
            ->first();
        if($document) {
            $body = ['code' => $code];
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->post(
                "{$this->baseUrl}/api/v2/external/document/" . $document->document_id . "/sign/pc-sms/" . $user->nopaper_certificate_id . "/confirm",
                $body
            );

            if ($response->successful()) {
                Document::query()
                    ->where('user_id', $user->id)
                    ->where('document_id', $document->document_id)
                    ->update([
                        'status_signature' => DocumentStatusSignatureEnum::Signed->value,
                        'date_signature' => Carbon::now()
                    ]);
                return ['success'=>true];
            }
            return ['error'=>true,'message'=>$response->json()];
        }
        return ['error'=>true,'message'=>[
            'description'=>'Document not found',
            'Name' => 'DocNotFound'
        ]];
    }

    public function retriesSms(User $user): array
    {
        $document = Document::query()
            ->where('user_id', $user->id)
            ->where('status', DocumentStatusEnum::Signed->value)
            ->where('status_signature', DocumentStatusSignatureEnum::Process->value)
            ->first();
        if($document) {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->post("{$this->baseUrl}/api/v2/external/document/".$document->document_id."/sign/pc-sms/".$user->nopaper_certificate_id);
            if ($response->successful()) {
                return ['success'=>true];
            } else {
                return ['error'=>true,'message'=>$response->json()];
            }
        }
        return ['error'=>true,'message'=>[
            'description'=>'Document not found',
            'Name' => 'DocNotFound'
        ]];
    }

    private function sendSms(User $user, $documentId){
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/".$documentId."/sign/pc-sms/".$user->nopaper_certificate_id);
        return $this->handleResponse($response);
    }

    public function registerUser(User $user)
    {
        $document = RecognitionDocument::query()
            ->where('user_id',$user->id)
            ->where('file_type',DocumentTypeEnum::Passport->value)
            ->orderBy('id','desc')
            ->first();
        /** @var RecognitionDocument $document */

        if ($document) {
            if(!empty($document->data['Sex']))
            {
                if($document->data['Sex'] == 'МУЖ') {
                    $sex = 1;
                }else{
                    $sex = 2;
                }
            }
            $payload = [
                'userPhone'           => $user->phone,
                'email'               => $user->email,
                'name'                => $document->data['FirstName']??'',
                'surname'             => $document->data['LastName']??'',
                'patronymic'          => $document->data['MiddleName'] ?? null, // Optional field
                'isShortTimePassword' => true,
                'birthDate'           => $document->data['BirthDate'] ? Carbon::parse($document->data['BirthDate'])->format('Y-m-d') :'',
                'gender'              => $sex ?? null,
                'passportData'        => [
                    'series'               => $document->data['Series']??'',
                    'number'               => $document->data['Number']??'',
                    'issuedBy'             => $document->data['GivenBy']??'',
                    'issuingDate'          => $document->data['GivenDate'] ? Carbon::parse($document->data['GivenDate'])->format('Y-m-d') :'',
                    'issuerDepartmentCode' => $document->data['SubdivisionCode']??'',
                    'birthPlace'           => $document->data['BirthPlace']??'',
                ]
            ];
        } else {
            return false;
        }

        if(!empty($payload)){
            $dataRegister = $this->createUser($payload);
            if($dataRegister && !empty($dataRegister['userGuid'])){
                $user->nopaper_guid = $dataRegister['userGuid'];
                $user->save();
                return true;
            }
        }
        return false;
    }

    private function createUser(array $payload): array
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/v2/external/profile-fl", $payload);

        return $this->handleResponse($response);
    }

    private function createSignature(string $userGuid): array
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/v2/external/certificate/pay-control/pc-sms".'?userGuid='.$userGuid.'&responsiblePartyForAcceptanceAct=1');
        return $this->handleResponse($response);
    }

    private function approveSignature(string $certificateId): array
    {
        sleep(1);
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->patch("{$this->baseUrl}/api/v2/external/certificate/pay-control/".$certificateId."/activate");
        return $this->handleResponse($response);
    }

    /**
     * Проверить наличие пользователя
     */
    public function checkUserExists(User $user)
    {
        $response     = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v2/external/profile-fl/user-guid/by-phone", [
            'userPhone' => $user->phone
        ]);
        $dataRegister = $this->handleResponse($response);
        if ($dataRegister && !empty($dataRegister['userGuid'])) {
            if(!$user->nopaper_guid || !$user->nopaper_certificate_id) {
                $certificateId = $this->createSignature($dataRegister['userGuid']);
                if(!empty($certificateId['certificateId'])) {
                    //$this->approveSignature($certificateId['certificateId']);
                    $user->nopaper_certificate_id = $certificateId['certificateId'];
                    $user->nopaper_guid = $dataRegister['userGuid'];
                    $user->save();
                    return true;
                }
                return false;
            }
            return true;
        }

        if($this->registerUser($user)){
            $certificateId = $this->createSignature($user->nopaper_guid);
            if(!empty($certificateId['certificateId'])) {
                //$this->approveSignature($certificateId['certificateId']);
                $user->nopaper_certificate_id = $certificateId['certificateId'];
                $user->save();
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Проверить наличие компании
     */
    public function checkCompanyExists($inn)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/api/v2/external/company/by-inn", [
            'inn' => $inn
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Создать черновик документа
     */
    public function createDraft($documentData)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/draft", $documentData);

        return $this->handleResponse($response);
    }

    /**
     * Прикрепить файл к документу
     */
    public function attachFileToDocument($documentId, $fileData)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/{$documentId}/file", $fileData);

        return $this->handleResponse($response);
    }

    /**
     * Отправить документ в работу
     */
    public function sendDocument($documentId): bool
    {
        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/api/v2/external/document/{$documentId}/send");

        if($response->status() === 200){
            return true;
        }

        return false;
    }

    /**
     * Получить информацию о документе
     */
    public function getDocumentInfo(Document $document): Document
    {
        if(!$document->file_path_signed) {
            $fileData = [
                'documentFileInfoList' => [
                    0 => [
                        'fileId'     => $document->file_id,
                        'documentId' => $document->document_id
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->post("{$this->baseUrl}/api/v2/external/document/file/list", $fileData);

            $fileData = $this->handleResponse($response);
            if (!empty($fileData['fileInfoList'])) {
                $fileContent = $fileData['fileInfoList'][0]['fileBase64'];
                $fileContent = base64_decode($fileContent);
                $fileName    = $fileData['fileInfoList'][0]['fileNameWithExtension'];
                $filePath    = '/source/document/' . $document->user_id . '/signed/' . date('Y-m-d') . '/' . $fileName;
                Storage::disk('public')->put($filePath, $fileContent, 'public');
                $document->file_path_signed = $filePath;
                $document->save();
            }
        }
        return $document;
    }

    /**
     * Обработка ответа API
     */
    protected function handleResponse(Response $response)
    {
        if ($response->successful()) {
            return $response->json();
        }
        return [];
    }
}
