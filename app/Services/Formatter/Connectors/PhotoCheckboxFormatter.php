<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class PhotoCheckboxFormatter implements FormaterInterface
{

    public static string $type= 'photoCheckbox';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = $value?:[];
        $option = [];
        if(!empty($fieldsData->valuesDirectory)){
            foreach ($fieldsData->valuesDirectory as $item) {
                $option[] = ['value'=>$item['uuid'],'label'=>$item['name'],'disabled'=>false];
            }
        }
        $data['options'] = $option;

        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
        $data['heading'] = $fieldsData->heading;
        //$data['error'];
        $data['dividerTop'] = $fieldsData->dividerTop;
        $data['dividerBottom'] = $fieldsData->dividerBottom;
        //$data['helperInfo'] = json_decode([],true);
        //$data['drawerInfo'] = json_decode([],true);






        return $data;



    }
}

