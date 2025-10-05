<?php
namespace App\Services\DocumentServices;

use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class RecognitionDocumentService
{
    private array $userData;
    private int $userId;

    const USER_FIELD_FOR_RECOGNITION = [
        'c5gAyG7YPWV7RiCx23srwQnYV8bv5U'
    ];

    public function __construct(array $userData, $userId)
    {
        $this->userData = $userData;
        $this->userId = $userId;
    }

    public function createDocumentForRecognition(): void
    {
        foreach (self::USER_FIELD_FOR_RECOGNITION as $field){
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
                $user->expansionData                             = $expansionData;
                $user->save();
            }
        }
    }
}
