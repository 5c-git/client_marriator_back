<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class AccountFormatter implements FormaterInterface
{

    public static string $type= 'account';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->uuid;
        if(!empty($fieldsData->updateData) && 0){
            $data['value'] = $fieldsData->updateData?:'';
        }else {
            $data['value'] = $value ?: '';
        }
        $data['label'] = $fieldsData->name?:'';
        $data['disabled'] = false;
        //$data['uuid'] = $fieldsData->uuid;

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
        if (!empty($fieldsData->helperInfo_link) && !empty($fieldsData->helperInfo_link_text)){
            $data['helperInfo']['link']['path'] = $fieldsData->helperInfo_link;
            $data['helperInfo']['link']['text'] = $fieldsData->helperInfo_link_text;
            $data['helperInfo']['link']['type'] = $fieldsData->helperInfo_link_type;
        }

        if(!empty($fieldsData->moreData)){
            $data['moreData'] = $fieldsData->moreData;
        }
        if(!empty($fieldsData->errorData)){
            $data['error'] = $fieldsData->errorData;
        }
//        if(!empty($fieldsData->updateData)){
//            $data['update'] = true;
//        }else{
//            $data['update'] = false;
//        }





        return $data;



    }
}

