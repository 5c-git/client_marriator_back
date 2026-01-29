<?php
namespace App\Services\DocumentServices;

use App\Enum\Document\DocumentFieldTypeEnum;
use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Organization;
use App\Models\Fields\Fields;
use App\Models\User;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class RecognitionDocumentService
{
    private array $userData;
    private array $userFieldForRecognition;
    private int $userId;
    private User $user;
    private Fields|null $fieldOrganization;

    public function __construct(array $userData,User $user)
    {
        $this->userFieldForRecognition = DocumentFieldTypeEnum::options();
        $this->userData = $userData;
        $this->userId = $user->id;
        $this->user = $user;
        $this->fieldOrganization = Fields::where('name','Список организаций')->first();
    }

    public function createDocumentForRecognition(): void
    {
        foreach ($this->userFieldForRecognition as $field){
            if(!empty($this->userData[$field])){
                $url = str_replace(config('app.url'), "", $this->userData[$field]);
                $recognitionDocument = new RecognitionDocument();
                $recognitionDocument->link = $url;
                $recognitionDocument->status = RecognitionDocumentStatusEnum::pending->value;
                $recognitionDocument->user_id = $this->userId;
                $recognitionDocument->file_field = $field;
                $recognitionDocument->save();
            }
        }
    }

    public function createDocument(): void
    {
        if(!empty($this->fieldOrganization) && !empty($this->userData[$this->fieldOrganization->uuid])){
            if(is_array($this->userData[$this->fieldOrganization->uuid])){
                $counterpartyId = [];
                $organization = Organization::whereIn('uuid',$this->userData[$this->fieldOrganization->uuid])->get();
                foreach ($organization as $item){
                    if($item->counterparty_id) {
                        $counterpartyId[] = $item->counterparty_id;
                    }
                }
                $counterparties = null;
                if(!empty($counterpartyId)){
                    $counterparties = Counterparty::whereIn('id',$counterpartyId)->get();
                }
                if($counterparties) {
                    $service = new UserDocumentCreatorService();
                    foreach ($counterparties as $counterparty) {
                        $service->createContract($this->user, $counterparty);
                    }
                }
            }
        }
    }

    public static function createUserMoreInformationInfoFromDocument(RecognitionDocument $recognitionDocument)
    {
        if($recognitionDocument->file_type) {
            if (is_array($recognitionDocument->data)) {
                $data = $recognitionDocument->data;
            } else {
                $data = json_decode($recognitionDocument->data, true);
            }
            /** @var User $user */
            $user = $recognitionDocument->user;

            if (is_array($user->data)) {
                $userData = $user->data;
            } else {
                $userData = json_decode($user->data, true);
            }

            if (!empty($userData[$recognitionDocument->file_field])) {
                if (is_array($user->expansionData)) {
                    $expansionData = $user->expansionData;
                } else {
                    $expansionData = json_decode($user->expansionData, true);
                }
                $dataForSave = [];
                $fileRecognition = $recognitionDocument->file_type->getRecognitionEnum();
                $documentFiledRecognitionArray = $fileRecognition::options();
                foreach ($data as $k => $field) {
                    if (!empty($documentFiledRecognitionArray[$k])) {
                        $dataForSave[] = [
                            'name'  => $fileRecognition::from($documentFiledRecognitionArray[$k])->getUserBinding(),
                            'value' => $field
                        ];
                    }
                }
                $expansionData[$recognitionDocument->file_field] = $dataForSave;
                $user->expansionData                             = json_encode($expansionData);
                $user->save();
            }
        }
    }

    public static function addErrorField(RecognitionDocument $recognitionDocument,string $errorText){
        /** @var User $user */
        $user = $recognitionDocument->user;
        if (is_array($user->data)) {
            $userData = $user->data;
        } else {
            $userData = json_decode($user->data, true);
        }

        if (!empty($userData[$recognitionDocument->file_field])) {
            if (is_array($user->errorData)) {
                $errorData = $user->errorData;
            } else {
                $errorData = json_decode($user->errorData, true);
            }

            $errorData[$recognitionDocument->file_field] = $errorText;
            $user->errorData                             = json_encode($errorData);
            $user->save();
        }

    }
}
