<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class EmailFormatter implements FormaterInterface
{

    public static string $type= 'email';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = $value?:'';
        $data['label'] = $fieldsData->label?:'';
        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
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

