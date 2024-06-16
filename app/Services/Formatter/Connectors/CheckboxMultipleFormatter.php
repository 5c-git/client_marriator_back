<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class CheckboxMultipleFormatter implements FormaterInterface
{

    public static string $type= 'checkboxMultiple';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['uuid'] = $fieldsData->uuid;
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = $value?:[];
        $option = [];
        foreach ($fieldsData->valuesDirectory as $item) {
            $option[] = ['value'=>$item['uuid'],'label'=>$item['name'],'disabled'=>false];
        }
        $data['options'] = $option;
        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
        if(!empty($fieldsData->heading)){
        $data['heading'] = $fieldsData->heading;
        }
        //$data['error'];
        $data['dividerTop'] = $fieldsData->dividerTop;
        $data['dividerBottom'] = $fieldsData->dividerBottom;
       // $data['helperInfo'] = json_decode([],true);


        //helperInfo




        return $data;



    }
}

