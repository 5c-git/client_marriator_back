<?php

namespace App\Services;

use App\Models\Fields\Fields;

class FormBuilderService
{

    public int $step = 1;
    public array $formData = [];

    public array $directory = [];
    public object $fieldsAll;
    public array $fieldsThisStep;
    public array $fieldsOldStep;

    public function __construct(int $step,array $formData = [])
    {
        $this->step = $step>0?$step:1;
        $this->formData = $formData;
    }

    public function createFormData(){
        $this->getFields();
        if($this->step>1){
            $this->filterFields();
        }else{
           // return $this->fieldsThisStep;
        }
        foreach ($this->fieldsThisStep as $tdgv){
            echo "<pre>";
            var_dump($tdgv->toArray());
            echo "</pre>";
        }
    }


    private function filterFields(){
        $oldFieldUuid = [];
        $formVal = array_flip($this->formData);
        foreach ($this->fieldsOldStep as $oldField){
            if(!empty($this->formData[$oldField->uuid]) || !empty($formVal[$oldField->uuid])) {
                $oldFieldUuid[] = $oldField->uuid;
            }
            if(!empty($oldField->directory)){
                foreach ($this->getDirectory($oldField->directory) as $directoryUuid) {
                    if (!empty($this->formData[$directoryUuid]) || !empty($formVal[$directoryUuid])) {
                        $oldFieldUuid[$oldField->uuid] = $directoryUuid;
                    }
                }
            }
        }
        foreach ($this->fieldsThisStep as $k=>$newFields){
           $parentFields = json_decode($newFields->parentFields,true);
           foreach ($parentFields as $parentField) {
               if (!in_array($parentField,$oldFieldUuid)){
                   unset($this->fieldsThisStep[$k]);
               }
           }
        }
    }

    private function getFields():void
    {
        $this->fieldsAll = Fields::get();
        foreach ($this->fieldsAll as $field){
            if(!empty($field->directory)){
                if($valuesDirectory = $this->getDirectory($field->directory,true)) {
                    $field->valuesDirectory = $valuesDirectory;
                }
                if($field->step == ($this->step-1) && !empty($this->formData[$field->uuid])){
                    if($fieldsFromDirectory = $this->getNextStepFieldsFromDirectory($field->directory,$this->formData[$field->uuid])) {
                        $this->fieldsThisStep = array_merge($this->fieldsThisStep, $fieldsFromDirectory);
                    }
                }
            }
            if($field->step == $this->step){
                $this->fieldsThisStep[] = $field;
            }elseif ($field->step < $this->step){
                $this->fieldsOldStep[] = $field;
            }

        }
    }

    private function getDirectory($directory,bool $allFields = false):array
    {
        $directoryData = [];
        if(class_exists($directory)) {
            if (empty($this->directory[$directory])) {
                $this->directory[$directory] = $directory::get();
            }
            foreach ($this->directory[$directory] as $directoryFields) {
                if($dataDirectoryFromObj = $directoryFields->getDataDirectory()){
                    $directoryData[] = $dataDirectoryFromObj;
                }
            }
        }
        return $directoryData;
    }

    private function getNextStepFieldsFromDirectory($directory,$value):array
    {
        $directoryFields = [];
        if(class_exists($directory)) {
            if (empty($this->directory[$directory])) {
                $this->directory[$directory] = $directory::get();
            }
            foreach ($this->directory[$directory] as $directoryFieldsObj) {
                if($dataDirectoryFromObj = $directoryFieldsObj->getDirectoryFields($value)){
                    foreach ($this->fieldsAll as $fields) {
                        if(in_array($fields->uuid,$dataDirectoryFromObj)) {
                            $directoryFields[] = $fields;
                        }
                    }
                }
            }
        }
        return $directoryFields;
    }


}
