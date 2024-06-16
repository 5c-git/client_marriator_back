<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class FileFormatter implements FormaterInterface
{

    public static string $type= 'file';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = $value?:'';

        $data['placeholder'] = $fieldsData->placeholder?:'';
        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
        $data['url'] = $value?:'';
        $data['heading'] = $fieldsData->heading;
        //$data['error'];
        $data['dividerTop'] = $fieldsData->dividerTop;
        $data['dividerBottom'] = $fieldsData->dividerBottom;
        //$data['helperInfo'] = json_decode([],true);
        //$data['drawerInfo'] = json_decode([],true);






        return $data;



    }
}

