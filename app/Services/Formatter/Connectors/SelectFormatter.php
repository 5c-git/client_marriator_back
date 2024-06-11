<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class SelectFormatter implements FormaterInterface
{

    public static string $type= 'select';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = $value?:'';
        $option = [];
        foreach ($fieldsData->valuesDirectory as $item) {
            $option[] = ['value'=>$item->uuid,'label'=>$item->name,'disabled'=>false];
        }
        $data['options'] = $option;
        $data['validation'] = 'none';
        if(!empty($fieldsData->heading)) {
            $data['heading'] = $fieldsData->heading;
        }
        $data['placeholder'] = $fieldsData->placeholder?:'';

        //$data['error'];
        $data['dividerTop'] = $fieldsData->dividerTop;
        $data['dividerBottom'] = $fieldsData->dividerBottom;
        // $data['helperInfo'] = json_decode([],true);


        //helperInfo




        return $data;



    }
}

