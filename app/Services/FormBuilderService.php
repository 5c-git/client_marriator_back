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
            foreach ($this->fieldsThisStep as $tdgv){
                echo "<pre>";
                var_dump($tdgv->toArray());
                echo "</pre>";
            }
           // return $this->fieldsThisStep;
        }
    }


    private function filterFields(){
        $oldFieldUuid = [];
        foreach ($this->fieldsOldStep as $oldField){
            $oldFieldUuid[] = $oldField->uuid;
            if(!empty($oldField->directory)){
                $oldFieldUuid = array_merge($oldFieldUuid, $this->getDirectory($oldField->directory));
            }
        }

    }

    private function getFields():void
    {
        $this->fieldsAll = Fields::get();
        foreach ($this->fieldsAll as $field){
            if(!empty($field->directory)){
                $field->valuesDirectory = $this->getDirectory($field->directory,true);
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
                if(!$allFields) {
                    $directoryData[] = $directoryFields->uuid;
                }else{
                    $directoryData[] = $directoryFields->toArray();
                }
            }
        }
        return $directoryData;
    }


}
