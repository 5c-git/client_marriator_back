<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class CheckBoxFormatter implements FormaterInterface
{

    public static string $type= 'checkbox';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->name?:'';
        $data['value'] = (bool)$value;
        $data['label'] = $fieldsData->label;//?
        $data['validation'] = 'none';//?
        $data['heading'] = $fieldsData->heading;//?

        //dividerTop
        //dividerBottom
        //helperInfo




        return $data;



    }
}

