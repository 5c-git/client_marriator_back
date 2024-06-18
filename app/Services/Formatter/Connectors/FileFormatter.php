<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;
use Illuminate\Support\Facades\Storage;

class FileFormatter implements FormaterInterface
{

    public static string $type= 'file';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        $data['uuid'] = $fieldsData->uuid;
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
        if(!empty($fieldsData->heading)) {
            $data['heading'] = $fieldsData->heading;
        }
        //$data['error'];
        $data['dividerTop'] = (bool)$fieldsData->dividerTop;
        $data['dividerBottom'] = (bool)$fieldsData->dividerBottom;
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

        if(!empty($fieldsData->drawerInfo_text)){
            $data['drawerInfo']['text'] = $fieldsData->drawerInfo_text;
        }
        if(!empty($fieldsData->drawerInfo_images)){
            foreach (json_decode($fieldsData->drawerInfo_images,true) as $fileImg){
                if(!empty($fileImg)) {
                    if (empty($data['drawerInfo']['images'])) {
                        $data['drawerInfo']['images'] = [];
                    }
                    $data['drawerInfo']['images'][] = Storage::url($fileImg);
                }
            }
        }
        //$data['drawerInfo'] = json_decode([],true);






        return $data;



    }
}

