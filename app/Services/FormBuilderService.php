<?php

namespace App\Services;

use App\Enum\Fields\PersonalInfoSectionEnum;
use App\Models\Fields\Fields;
use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormBuilderService
{

    public int $step = 1;
    public array $formData = [];
    public array $formDataThisStep = [];

    public array $directory = [];
    public array $filterArr = [];
    public object $fieldsAll;
    public array $fieldsThisStep = [];
    public array $fieldsOldStep = [];
    public array $formatedData = [];
    public array $moreData = [];
    public array $errorData = [];
    public array $updateData = [];

    public function __construct(int $step, array $formData = [])
    {
        $this->step = $step > 0 ? $step : 1;
        if(!empty($formData) && (!empty($formData[0]) || !empty($formData[1]) || !empty($formData[2]) || !empty($formData[3]) || !empty($formData[4]) || !empty($formData[5]) || !empty($formData[6])  || !empty($formData[7])  || !empty($formData[8]))) {
            try {
                $this->formData = array_merge(...$formData);
                if(!empty($formData[$step])) {
                    $this->formDataThisStep = $formData[$step];
                }else{
                    $this->formDataThisStep = [];
                }
            }catch (\Throwable $e){
                foreach ($formData as $k=>$data){
                    if(in_array($k,[1,2,3,4,5,6,7,8])){
                        $formData = array_merge($formData,$data);
                        unset($formData[$k]);
                    }
                }
                $this->formData = $formData;
                $this->formDataThisStep = $formData;
            }
        }else{
            $this->formData = $formData;
            $this->formDataThisStep = $formData;
        }

    }

    public function setDataUser(array $moreData,array $errorData,array $updateData,array $changefields){
        $this->errorData = $errorData;
        $this->moreData = $moreData;
        $this->updateData = $updateData;
        $this->formData = $changefields+$this->formData;
        $this->formDataThisStep = $changefields+$this->formDataThisStep;
    }

    public function createFormData(array $moreData = [],array $errorData = [],array $updateData = []): array
    {
        $this->errorData = $errorData;
        $this->moreData = $moreData;
        $this->updateData = $updateData;

        $this->getFilterArr();
        $this->getFields();
        $this->filterFields();
        return $this->formatData($this->fieldsThisStep);
    }

    public function createPersonalUserFormData($section):array
    {
        $this->getFilterArr();
        $this->getAllFields($section);
        $this->filterFields();
        return $this->formatData($this->fieldsThisStep,true);
    }

    public function getStepField()
    {
        $this->getFilterArr();
        $this->getFields();
        $this->filterFields();
    }


    private function filterFields(): void
    {
        foreach ($this->fieldsThisStep as $k => $newFields) {
            $unset = false;
            $parentFields = json_decode($newFields->parentFields, true);
            foreach ($parentFields as $parentField) {
                $unset = false;
                foreach ($parentField as $oneField) {
                    if (!in_array($oneField, $this->filterArr,true)) {
                        $unset = true;
                    }
                }
                if (!$unset) {
                    break;
                }
            }
            if ($unset) {
                unset($this->fieldsThisStep[$k]);
            }

        }
    }

    private function getFilterArr(){
        $formVal = [];
        foreach ($this->formData as $kDataForm => $formData) {
            if(!empty($formData)) {
                if (is_array($formData)) {
                    foreach ($formData as $oneData) {
                        if(!empty($oneData)) {
                            if(is_array($oneData)){
                                foreach ($oneData as $oneDataNew){
                                    if(!empty($oneDataNew)){
                                        $formVal[$oneDataNew.$kDataForm.Str::random(10)] = $oneDataNew;
                                    }
                                }
                            }else {
                                $formVal[$oneData.Str::random(10)] = $kDataForm;
                            }
                            $formVal[$kDataForm.Str::random(10)] = $oneData;
                        }
                    }
                } else {
                    $formVal[$formData.Str::random(10)] = $kDataForm;
                }
                $formVal[$kDataForm.Str::random(10)] = $formData;
            }
        }
        $this->filterArr = $formVal;
    }

    private function getFields(): void
    {
        $user = Auth::user();
        $userRoles = $user?->roles->pluck('id')->toArray();
        $this->fieldsAll = Fields::query()->orderBy('sort', 'asc')
            ->where('active',true)
            ->whereNotNull('step')
            ->when(!empty($userRoles), function (Builder $q, array $userRoles) {
                $q->whereHas('roles', function ($query) use ($userRoles)  {
                    $query->whereIn('user_role_id', $userRoles);
                })->orWhereDoesntHave('roles');
            })
            ->get();
        foreach ($this->fieldsAll as $field) {
            if (!empty($field->directory)) {
                $field->oldType = $field->type;
                $field->type = $this->getTypeDirectory($field->directory);
                if ($valuesDirectory = $this->getDirectory($field->directory, true)) {
                    $field->valuesDirectory = $valuesDirectory;
                }else{
                    $field->valuesDirectory = [];
                }
            }
            if($field->oldType == FieldsTypeEnum::directory->value && empty($field->valuesDirectory)){
                continue;
            }
            if ($field->step == $this->step) {
                $this->fieldsThisStep[] = $field;
            } elseif ($field->step <= $this->step) {
                $this->fieldsOldStep[] = $field;
            }

        }
    }

    private function getAllFields($section): void
    {
        $user = Auth::user();
        $userRoles = $user?->roles->pluck('id')->toArray();
        $this->fieldsAll = Fields::query()
            ->orderBy('sort', 'asc')
            ->where('active',true)
            ->where('section',$section)
            ->whereNotNull('step')
            ->when(!empty($userRoles), function (Builder $q, array $userRoles) {
                $q->whereHas('roles', function ($query) use ($userRoles)  {
                    $query->whereIn('user_role_id', $userRoles);
                })->orWhereDoesntHave('roles');
            })
            ->get();
        foreach ($this->fieldsAll as $field) {
            if (!empty($field->directory)) {
                $field->oldType = $field->type;
                $field->type = $this->getTypeDirectory($field->directory);
                if ($valuesDirectory = $this->getDirectory($field->directory, true)) {
                    $field->valuesDirectory = $valuesDirectory;
                }else{
                    $field->valuesDirectory = [];
                }
            }
            if($field->oldType == FieldsTypeEnum::directory->value && empty($field->valuesDirectory)){
                continue;
            }

            $this->fieldsThisStep[] = $field;
            $this->fieldsOldStep[] = $field;
        }
    }

    private function getDirectory($directory, bool $allFields = false): array
    {
        $directoryData = [];
        if (class_exists($directory)) {
            if (empty($this->directory[$directory])) {
                $this->directory[$directory] = $directory::where('active',true)->get();
            }
            foreach ($this->directory[$directory] as $directoryFields) {
                if ($dataDirectoryFromObj = $directoryFields->getDataDirectory($allFields,$this->filterArr)) {
                    $directoryData[] = $dataDirectoryFromObj;
                }
            }
        }
        return $directoryData;
    }

    private function getTypeDirectory($directory): int
    {
        if (class_exists($directory)) {
            return $directory::$fieldsTypeEnum;
        } else {
            return 0;
        }
    }

    private function formatData($data,$personal = false): array
    {
        foreach ($data as $field) {
            if(!empty($this->formData[$field->uuid])){
                $value = $this->formData[$field->uuid];
            }else{
                $value = '';
            }
            if(!empty($this->moreData[$field->uuid])){
                $field->moreData = $this->moreData[$field->uuid];
            }
            if(!empty($this->errorData[$field->uuid])){
                $field->errorData = $this->errorData[$field->uuid];
            }
            if(isset($this->updateData[$field->uuid])){
                $field->updateData = $this->updateData[$field->uuid];
            }

            if ($field->type != FieldsTypeEnum::directory->value) {
                if ($fieldDataFormat = FieldsTypeEnum::from($field->type)?->typeClassFormatter()::createFormat($field, $value)) {
                    $this->formatedData[] = $fieldDataFormat;
                }
            }
        }
        return $this->formatedData;
    }

    public function checkStatusForm(bool $getForm = false,$formData = []):string
    {
        if(empty($formData)) {
            $statusForm = 'needRequired';
            if (!empty($this->fieldsThisStep) && !empty($this->formDataThisStep)) {
                if ($this->checkRequired()) {
                    $statusForm = 'allowedNewStep';
                }

                if (count($this->formDataThisStep) < count($this->fieldsThisStep) && !$getForm) {
                    $statusForm = 'addedNewFields';
                }
            }
        }else{
            $statusForm = 'needRequired';
            if (!empty($this->fieldsThisStep) && !empty($formData)) {
                if ($this->checkRequired()) {
                    $statusForm = 'allowedNewStep';
                }

                if (count($formData) < count($this->fieldsThisStep) && !$getForm) {
                    $statusForm = 'addedNewFields';
                }
            }
        }
        return $statusForm;
    }

    public function checkRequired(): bool
    {
        $required = true;
        foreach($this->fieldsThisStep as $data){
            if((empty($field->oldType) && $data->type != FieldsTypeEnum::directory->value) || ($data->oldType == FieldsTypeEnum::directory->value && !empty($data->directory) && !empty($data->valuesDirectory)) ) {
                if ($data->required && empty($this->formDataThisStep[$data->uuid])) {
                    $required = false;
                    break;
                }
            }
        }
        return $required;
    }

    public function getUserField(array $moreData,array $errorData): array
    {
        $user = Auth::user();
        $userRoles = $user?->roles->pluck('id')->toArray();
        $this->fieldsAll = Fields::query()
            ->orderBy('step', 'asc')
            ->where('active',true)
            ->whereNotNull('step')
            ->when(!empty($userRoles), function (Builder $q, array $userRoles) {
                $q->whereHas('roles', function ($query) use ($userRoles)  {
                    $query->whereIn('user_role_id', $userRoles);
                })->orWhereDoesntHave('roles');
            })
            ->orderBy('sort', 'asc')
            ->get();
        $userFields = [];
        foreach ($this->fieldsAll as $field) {
            if (!empty($field->directory)) {
                $field->oldType = $field->type;
                $field->type = $this->getTypeDirectory($field->directory);
                if ($valuesDirectory = $this->getDirectory($field->directory, true)) {
                    $field->valuesDirectory = $valuesDirectory;
                }else{
                    $field->valuesDirectory = [];
                }
            }
            if($field->oldType == FieldsTypeEnum::directory->value && empty($field->valuesDirectory)){
                continue;
            }

            if(isset($this->formDataThisStep[$field->uuid])){
                if(!empty($field->valuesDirectory)){
                    $field->value = '';
                    foreach ($field->valuesDirectory as $valueDerictory){
                        if(is_array($this->formDataThisStep[$field->uuid])){
                            if(in_array($valueDerictory['uuid'],$this->formDataThisStep[$field->uuid])){
                                if(empty($field->value)) {
                                    $field->value = $valueDerictory['name'];
                                }else{
                                    $field->value .= ', '.$valueDerictory['name'];
                                }
                            }
                        }else{
                            if($valueDerictory['uuid'] == $this->formDataThisStep[$field->uuid]) {
                                $field->value = $valueDerictory['name'];
                            }
                        }
                    }
                }else{
                    $field->value = $this->formDataThisStep[$field->uuid];
                }
                if(!empty($moreData[$field->uuid])){
                    $field->moreData = $moreData[$field->uuid];
                }
                if(!empty($errorData[$field->uuid])){
                    $field->errorData = $errorData[$field->uuid];
                }
                $userFields[] = $field;
            }
        }
        return $userFields;
    }

    public static function getUserMenu(array $userError = []):array
    {
        $sectionDots = [];
        $menuSection = [];
        $user = Auth::user();
        $userRoles = $user?->roles->pluck('id')->toArray();
        if (!empty($userError)) {
            $fieldsUuid = [];
            foreach ($userError as $uuid => $errorFieldData) {
                $fieldsUuid[] = $uuid;
            }
            if (!empty($fieldsUuid)) {
                $fieldSections = Fields::query()
                    ->whereIn('uuid', $fieldsUuid)
                    ->where('active',true)
                    ->where('section', '>', 0)
                    ->when(!empty($userRoles), function (Builder $q, array $userRoles) {
                        $q->whereHas('roles', function ($query) use ($userRoles)  {
                            $query->whereIn('user_role_id', $userRoles);
                        })->orWhereDoesntHave('roles');
                    })
                    ->selectRaw('section')
                    ->get();
                foreach ($fieldSections as $fieldSection) {
                    $sectionDots[] = $fieldSection->section;
                }
            }
        }
        foreach (PersonalInfoSectionEnum::options() as $k => $option) {
            $dataSection = [
                'name' => PersonalInfoSectionEnum::from($option)->typeName(),
                'value' => $option
            ];
            if(in_array($option,$sectionDots)){
                $dataSection['notification'] = true;
            }else{
                $dataSection['notification'] = false;
            }
            $menuSection[] = $dataSection;
        }
        return $menuSection;
    }

}
