<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;

class BicFormatter implements FormaterInterface
{

    public static string $type= 'bic';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];

        if(!empty($fieldsData->valuesDirectory)) {
            //$data['uuid'] = $fieldsData->uuid;
            $data['inputType'] = self::$type;
            $data['name'] = $fieldsData->uuid;
            if(isset($fieldsData->updateData)){
                $data['value'] = $fieldsData->updateData?:'';
            }else {
                $data['value'] = $value ?: '';
            }
            $checkData = false;
            foreach ($fieldsData->valuesDirectory as $item) {
                if($item['uuid'] == $data['value']){
                    $checkData = true;
                    break;
                }
            }
            if(!$checkData){
                $data['value'] = '';
            }

            $data['placeholder'] = $fieldsData->placeholder?:'';
            $data['disabled'] = false;
            $option = [];
            foreach ($fieldsData->valuesDirectory as $item) {
                $option[$item['name']] = ['value' => $item['uuid'], 'bic'=>$item['bic'], 'label' => $item['name'], 'disabled' => false];
            }
            ksort($option);
            $data['options'] = array_values($option);
            if ($fieldsData->required) {
                $data['validation'] = 'default';
            } else {
                $data['validation'] = 'none';
            }
            if (!empty($fieldsData->heading)) {
                $data['heading'] = $fieldsData->heading;
            }
            //$data['error'];
            if (!empty($fieldsData->dividerTop)) {
                $data['dividerTop'] = (bool)$fieldsData->dividerTop;
            }
            if (!empty($fieldsData->dividerBottom)) {
                $data['dividerBottom'] = (bool)$fieldsData->dividerBottom;
            }
            if (!empty($fieldsData->helperInfo_text)) {
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

            if(!$data['value']) {
                if (!empty($fieldsData->default_value)) {
                    $data['value'] = $fieldsData->default_value;
                } else {
                    if (!empty($fieldsData->default)) {
                        $data['value'] = $fieldsData->default;
                    }
                }
            }

            if(!empty($fieldsData->preg_value)){
                $data['pregValue'] = $fieldsData->preg_value;
                $data['pregText'] = $fieldsData->preg_text;
            }

            if(isset($fieldsData->updateData)){
                $data['status'] = "warning";
                $data['disabled'] = true;
                $data['helperInfo'] = 'Значение поля находится на модерации';
                //$data['update'] = true;
            }else{
                //$data['update'] = false;
            }
        }

        return $data;



    }
}

