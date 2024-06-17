<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class CheckBoxFormatter implements FormaterInterface
{

    public static string $type= 'checkbox';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['uuid'] = $fieldsData->uuid;
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = (bool)$value;
        $data['label'] = $fieldsData->label?:'';
        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
        if(!empty($fieldsData->heading)) {
            $data['heading'] = $fieldsData->heading;
        }
        //$data['error'];
        $data['dividerTop'] = (bool)$fieldsData->dividerTop;
        $data['dividerBottom'] = (bool)$fieldsData->dividerBottom;
       // $data['helperInfo'] = json_decode([],true);


        //helperInfo




        return $data;



    }
}

