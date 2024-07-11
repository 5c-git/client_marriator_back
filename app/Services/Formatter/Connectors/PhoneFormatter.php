<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class PhoneFormatter implements FormaterInterface
{

    public static string $type= 'phone';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        //$data['uuid'] = $fieldsData->uuid;
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->uuid;
        $data['value'] = $value?:'';
        $data['label'] = $fieldsData->name?:'';
        $data['disabled'] = false;
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
        if(!empty($fieldsData->dividerTop)) {
            $data['dividerTop'] = (bool)$fieldsData->dividerTop;
        }
        if(!empty($fieldsData->dividerBotto)) {
            $data['dividerBottom'] = (bool)$fieldsData->dividerBottom;
        }
        if (!empty($fieldsData->helperInfo_text)){
            $data['helperInfo']['text'] = $fieldsData->helperInfo_text;
        }
        if (!empty($fieldsData->helperInfo_link)){
            $data['helperInfo']['link']['path'] = $fieldsData->helperInfo_link;
            if(!empty($fieldsData->helperInfo_link_text)){
                $data['helperInfo']['link']['text'] = $fieldsData->helperInfo_link_text;
            }
            $data['helperInfo']['link']['type'] = $fieldsData->helperInfo_link_type;
        }

        if(!empty($fieldsData->moreData)){
            $data['moreData'] = $fieldsData->moreData;
        }
        if(!empty($fieldsData->errorData)){
            $data['errorData'] = $fieldsData->errorData;
        }




        return $data;



    }
}

