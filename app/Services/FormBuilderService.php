<?php

namespace App\Services;

use App\Models\Fields\Fields;
use App\Enum\Fields\FieldsTypeEnum;

class FormBuilderService
{

    public int $step = 1;
    public array $formData = [];
    public array $formDataThisStep = [];

    public array $directory = [];
    public object $fieldsAll;
    public array $fieldsThisStep = [];
    public array $fieldsOldStep = [];
    public array $formatedData = [];

    public function __construct(int $step, array $formData = [])
    {
        $this->step = $step > 0 ? $step : 1;
        if(!empty($formData)) {
            $this->formData = array_merge(...$formData);
        }
        if(!empty($formData[$step])) {
            $this->formDataThisStep = $formData[$step];
        }else{
            $this->formDataThisStep = [];
        }
    }

    public function createFormData(): array
    {
        $this->getFields();
        $this->filterFields();
        return $this->formatData($this->fieldsThisStep);
    }

    public function getStepField()
    {
        $this->getFields();
        $this->filterFields();
    }


    private function filterFields(): void
    {
        $oldFieldUuid = [];
        foreach ($this->formData as $kDataForm => $formData) {
            if(!empty($formData)) {
                if (is_array($formData)) {
                    foreach ($formData as $oneData) {
                        $formVal[$oneData] = $kDataForm;
                    }
                } else {
                    $formVal[$formData] = $kDataForm;
                }
            }
        }
        foreach ($this->fieldsOldStep as $oldField) {
            if (!empty($this->formData[$oldField->uuid]) || !empty($formVal[$oldField->uuid])) {
                $oldFieldUuid[] = $oldField->uuid;
            }
            if (!empty($oldField->directory)) {
                foreach ($this->getDirectory($oldField->directory) as $directoryUuid) {
                    if (!empty($this->formData[$directoryUuid]) || !empty($formVal[$directoryUuid])) {
                        $oldFieldUuid[$oldField->uuid] = $directoryUuid;
                    }
                }
            }
        }
        foreach ($this->fieldsThisStep as $k => $newFields) {
            $unset = false;
            $parentFields = json_decode($newFields->parentFields, true);
            foreach ($parentFields as $parentField) {
                $unset = false;
                foreach ($parentField as $oneField) {
                    if (!in_array($oneField, $oldFieldUuid)) {
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

    private function getFields(): void
    {
        $this->fieldsAll = Fields::orderBy('sort', 'asc')->get();
        foreach ($this->fieldsAll as $field) {
            if (!empty($field->directory)) {
                $field->type = $this->getTypeDirectory($field->directory);
                if ($valuesDirectory = $this->getDirectory($field->directory, true)) {
                    $field->valuesDirectory = $valuesDirectory;
                }else{
                    $field->valuesDirectory = [];
                }
            }
            if($field->type == FieldsTypeEnum::directory->value && empty($field->valuesDirectory)){
                continue;
            }
            if ($field->step == $this->step) {
                $this->fieldsThisStep[] = $field;
            } elseif ($field->step <= $this->step) {
                $this->fieldsOldStep[] = $field;
            }

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
                if ($dataDirectoryFromObj = $directoryFields->getDataDirectory($allFields)) {
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

    private function formatData($data): array
    {
        foreach ($data as $field) {
            if(!empty($this->formData[$field->uuid])){
                $value = $this->formData[$field->uuid];
            }else{
                $value = '';
            }

            if ($field->type != FieldsTypeEnum::directory->value) {
                if ($fieldDataFormat = FieldsTypeEnum::from($field->type)?->typeClassFormatter()::createFormat($field, $value)) {
                    $this->formatedData[] = $fieldDataFormat;
                }
            }
        }
        return $this->formatedData;
    }

    public function checkStatusForm(bool $getForm = false):string
    {
        $statusForm = 'needRequired';
        if(!empty($this->fieldsThisStep) && !empty($this->formDataThisStep)){
            if($this->checkRequired()){
                $statusForm = 'allowedNewStep';
            }

            if(count($this->formDataThisStep) < count($this->fieldsThisStep) && !$getForm){
                $statusForm = 'addedNewFields';
            }
        }
        return $statusForm;
    }

    public function checkRequired(): bool
    {
        $required = true;
        foreach($this->fieldsThisStep as $data){
            if($data->type != FieldsTypeEnum::directory->value || ($data->type == FieldsTypeEnum::directory->value && !empty($data->directory) && !empty($data->valuesDirectory)) )
            if($data->required && empty($this->formDataThisStep[$data->uuid])){
                $required = false;
                break;
            }
        }
        return $required;
    }

}
