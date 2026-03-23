<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;
use Illuminate\Support\Facades\Storage;

class PhotoFormatter implements FormaterInterface
{

    public static string $type= 'photo';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        //$data['uuid'] = $fieldsData->uuid;
        $data['inputType'] = self::$type;
        $data['name'] = $fieldsData->uuid;
        if(isset($fieldsData->updateData)){
            $data['value'] = $fieldsData->updateData?:'';
        }else {
            $data['value'] = $value?:'';
        }

        $data['disabled'] = false;
        $data['placeholder'] = $fieldsData->placeholder?:'';
        if($fieldsData->required){
            $data['validation'] = 'default';
        }else{
            $data['validation'] = 'none';
        }
        $data['url'] = config('app.url').'/api/saveUserImg/';
        if(!empty($fieldsData->heading)) {
            $data['heading'] = $fieldsData->heading;
        }
        //$data['error'];
        if(!empty($fieldsData->dividerTop)) {
            $data['dividerTop'] = (bool)$fieldsData->dividerTop;
        }
        if (!empty($fieldsData->dividerBottom)) {
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

        if(!empty($fieldsData->drawerInfo_text)){
            $data['drawerInfo']['text'] = $fieldsData->drawerInfo_text;
        }
        if(!empty($fieldsData->drawerInfo_images)){
            foreach (json_decode($fieldsData->drawerInfo_images,true) as $fileImg){
                if(!empty($fileImg)) {
                    if (empty($data['drawerInfo']['images'])) {
                        $data['drawerInfo']['images'] = [];
                    }
                    $data['drawerInfo']['images'][] = config('app.url').Storage::url($fileImg);
                }
            }
        }
        //$data['drawerInfo'] = json_decode([],true);
        if(!empty($fieldsData->moreData)){
            $data['moreData'] = $fieldsData->moreData;
        }
        if(!empty($fieldsData->errorData)){
            $data['error'] = $fieldsData->errorData;
        }

        if(!empty($fieldsData->default_value)){
            $data['default'] = $fieldsData->default_value;
        }else {
            if (!empty($fieldsData->default)) {
                $data['default'] = $fieldsData->default;
            }
        }

        if(isset($fieldsData->updateData)){
            $data['status'] = "warning";
            $data['disabled'] = true;
            $data['helperInfo'] = 'Значение поля находится на модерации';
            //$data['update'] = true;
        }else{
            //$data['update'] = false;
        }



        return $data;



    }
}

