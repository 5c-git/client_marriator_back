<?php

namespace App\Services\User;

use App\Enum\Document\DocumentFieldRecognition\Passport;
use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use App\Enum\Document\DocumentTypeEnum;
use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Fields\Directory\Age;
use App\Models\Fields\Directory\Gender;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Fields;
use App\Models\User;
use Carbon\Carbon;

class UserDataService
{
    private array $moreInfo = [];
    private array $moreInfoField = [];
    protected array $settings = [];
    private User $user;
    private array $passportData = [];

    public function __construct()
    {
        $this->getMoreInformation();
    }

    private function getMoreInformation(): void
    {
        if(!$this->moreInfo) {
            $this->moreInfo['addressReg']  = Fields::where('name', 'Адрес регистрации')->first();
            $this->moreInfo['name']  = Fields::where('name', 'Имя')->first();
            $this->moreInfo['lastName']  = Fields::where('name', 'Фамилия')->first();
            $this->moreInfo['secondName']  = Fields::where('name', 'Отчество')->first();
            $this->moreInfo['gender']  = Fields::where('directory', Gender::class)->first();
            $this->moreInfo['taxStatus']  = Fields::where('directory',TaxStatus::class)->first();

            $this->moreInfoField['gender']=Gender::get()->keyBy('uuid')->toArray();
            $this->moreInfoField['taxStatus']=TaxStatus::get()->keyBy('uuid')->toArray();
        }
    }

    private function getFieldView($name)
    {
        $data = '';
        if(!empty($this->user->data[$this->moreInfo[$name]->uuid])) {
            if (is_array($this->user->data[$this->moreInfo[$name]->uuid])) {
                $data = [];
                foreach ($this->user->data[$this->moreInfo[$name]->uuid] as $field) {
                    if (!empty($this->moreInfoField[$name][$field]['name'])) {
                        $data[] = $this->moreInfoField[$name][$field]['name'];
                    } else {
                        $data[] = $field;
                    }
                }
            } else {
                if (!empty($this->moreInfoField[$name][$this->user->data[$this->moreInfo[$name]->uuid]]['name'])) {
                    $data = $this->moreInfoField[$name][$this->user->data[$this->moreInfo[$name]->uuid]]['name'];
                } else {
                    $data = $this->user->data[$this->moreInfo[$name]->uuid];
                }
            }
        }
        return $data;
    }

    public function getName(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name') . ' ' .$this->getFieldView('lastName'). ' ' .$this->getFieldView('secondName'));

        if(empty($name)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $name =  $this->passportData[Passport::LastName->name].' '.$this->passportData[Passport::FirstName->name].' '.$this->passportData[Passport::MiddleName->name];
            }
        }

        return $name??null;
    }

    public function getOnlyName(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name'));
        if(empty($name)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $name =  $this->passportData[Passport::FirstName->name];
            }
        }
        return $name??null;
    }

    public function getShortName(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name'));
        $lastName = trim($this->getFieldView('lastName'));
        $secondName = trim($this->getFieldView('secondName'));
        if(!empty($name) && !empty($lastName) && !empty($secondName)){
            $returnData = $lastName.' '.mb_substr($name, 0, 1, 'UTF-8').'. '.mb_substr($secondName, 0, 1, 'UTF-8').'.';
        }

        if(empty($returnData)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $returnData =  $this->passportData[Passport::LastName->name].' '.mb_substr($this->passportData[Passport::FirstName->name], 0, 1, 'UTF-8').'. '.mb_substr($this->passportData[Passport::MiddleName->name], 0, 1, 'UTF-8').'.';
            }
        }


        return $returnData??null;
    }

    public function getTaxStatusName(User $user):string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $taxStatus = trim($this->getFieldView('taxStatus'));
        if(!empty($taxStatus) && $taxStatus =='Самозанятый'){
            return 'плательщик налога на профессиональный доход (самозанятый)';
        }else{
            return 'физическое лицо';
        }
    }

    public function getTaxStatusTemplate(User $user):DocumentTemplatesEnum
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $taxStatus = trim($this->getFieldView('taxStatus'));
        if(!empty($taxStatus) && $taxStatus =='Самозанятый'){
            return DocumentTemplatesEnum::offer;
        }else{
            return DocumentTemplatesEnum::offerSelf;
        }
    }

    public function getShortNameOnliLaters(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name'));
        $secondName = trim($this->getFieldView('secondName'));
        if(!empty($name) && !empty($secondName)){
            $returnData = mb_substr($name, 0, 1, 'UTF-8').'. '.mb_substr($secondName, 0, 1, 'UTF-8').'.';
        }

        if(empty($returnData)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $returnData =  mb_substr($this->passportData[Passport::FirstName->name], 0, 1, 'UTF-8').'. '.mb_substr($this->passportData[Passport::MiddleName->name], 0, 1, 'UTF-8').'.';
            }
        }

        return $returnData??null;
    }

    public function getShortNameOnliLatersWithLastName(User $user): string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('name'));
        $secondName = trim($this->getFieldView('secondName'));
        $lastName = trim($this->getFieldView('lastName'));
        if(!empty($lastName) && !empty($name) && !empty($secondName)){
            $returnData = $lastName. ' ' . mb_substr($name, 0, 1, 'UTF-8') . '.'.mb_substr($secondName, 0, 1, 'UTF-8') . '.';
        }

        if(empty($returnData)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $returnData =  $this->passportData[Passport::LastName->name].' '.mb_substr($this->passportData[Passport::FirstName->name], 0, 1, 'UTF-8').'. '.mb_substr($this->passportData[Passport::MiddleName->name], 0, 1, 'UTF-8').'.';
            }
        }

        return $returnData??'не заполнено';
    }

    public function getOnlyLastName(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('lastName'));
        if(empty($name)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $name =  $this->passportData[Passport::LastName->name];
            }
        }
        return $name??null;
    }

    public function getOnlySecondName(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $name = trim($this->getFieldView('secondName'));
        if(!$name){
            $name = $this->user->name;
        }

        if(empty($name)) {
            if (empty($this->passportData)) {
                $document = RecognitionDocument::query()
                    ->where('user_id', $this->user->id)
                    ->where('file_type', DocumentTypeEnum::Passport->value)
                    ->orderBy('id', 'desc')
                    ->first();
                /** @var RecognitionDocument $document */
                $this->passportData = $document->data;
            }
            if(!empty($this->passportData)) {
                $name = $this->passportData[Passport::MiddleName->name];
            }
        }


        return $name??null;
    }

    public function getGender(User $user): ?string
    {
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $gender = trim($this->getFieldView('gender'));
        return $gender??null;
    }

    public function getBirthDate(User $user): ?string
    {
        $this->user = $user;
        if(empty($this->passportData)){
            $document = RecognitionDocument::query()
                ->where('user_id',$this->user->id)
                ->where('file_type',DocumentTypeEnum::Passport->value)
                ->orderBy('id','desc')
                ->first();
            /** @var RecognitionDocument $document */
            $this->passportData = $document->data;
        }
        if(!empty($this->passportData)){
            return $this->passportData[Passport::BirthDate->name];
        }
        return null;
    }

    public function getBirthDateYear(User $user): ?string
    {
        $this->user = $user;
        if(empty($this->passportData)){
            $document = RecognitionDocument::query()
                ->where('user_id',$this->user->id)
                ->where('file_type',DocumentTypeEnum::Passport->value)
                ->orderBy('id','desc')
                ->first();
            /** @var RecognitionDocument $document */
            $this->passportData = $document->data;
        }
        if(!empty($this->passportData) && !empty($this->passportData[Passport::BirthDate->name])){
            return Carbon::parse($this->passportData[Passport::BirthDate->name])->format('Y');
        }
        return null;
    }

    public function getPassportDetails(User $user): ?string
    {
        $this->user = $user;
        if(empty($this->passportData)){
            $document = RecognitionDocument::query()
                ->where('user_id',$this->user->id)
                ->where('file_type',DocumentTypeEnum::Passport->value)
                ->orderBy('id','desc')
                ->first();
            /** @var RecognitionDocument $document */
            $this->passportData = $document->data;
        }
        if(!empty($this->passportData)){
            return $this->passportData[Passport::Series->name].' '.$this->passportData[Passport::Number->name];
        }
        return null;
    }

    public function getIsHavePassport(User $user): bool
    {
        $this->user = $user;
        if(empty($this->passportData)){
            $document = RecognitionDocument::query()
                ->where('user_id',$this->user->id)
                ->where('file_type',DocumentTypeEnum::Passport->value)
                ->orderBy('id','desc')
                ->first();
            /** @var RecognitionDocument $document */
            $this->passportData = $document->data;
        }
        if(!empty($this->passportData)){
            return true;
        }
        return false;
    }

    public function getPassportIssuer(User $user): ?string
    {
        $this->user = $user;
        if(empty($this->passportData)){
            $document = RecognitionDocument::query()
                ->where('user_id',$this->user->id)
                ->where('file_type',DocumentTypeEnum::Passport->value)
                ->orderBy('id','desc')
                ->first();
            /** @var RecognitionDocument $document */
            $this->passportData = $document->data;
        }
        if(!empty($this->passportData)){
            return $this->passportData[Passport::GivenBy->name];
        }
        return null;
    }

    public function getRegistrationAddress(User $user): ?string
    {
        return 'не заполнено (registrationAddress)';
        $this->user = $user;
        if(!is_array($this->user->data)){
            $this->user->data = json_decode($this->user->data,true);
        }
        $address = trim($this->getFieldView('addressReg'));
        return $address??null;
    }

    static function getUserSnils(User $user):?string
    {
        $field = Fields::where('type', FieldsTypeEnum::snils->value)->first();
        if(is_array($user->data)){
            $data = $user->data;
        }else{
            $data = json_decode($user->data,true);
        }
        if(!empty($data[$field->uuid])){
            return $data[$field->uuid];
        }
        return null;
    }
}
