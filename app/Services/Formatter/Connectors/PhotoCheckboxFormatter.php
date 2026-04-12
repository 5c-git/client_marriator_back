<?php
namespace App\Services\Formatter\Connectors;

use App\Services\Formatter\FormaterInterface;
use Illuminate\Support\Facades\Storage;

class PhotoCheckboxFormatter implements FormaterInterface
{

    public static string $type= 'photoCheckbox';

    public static function createFormat($fieldsData,$value):array
    {
        $data = [];
        if(!empty($value) && !is_array($value)){
            $value = [$value];
        }
        if(!empty($fieldsData->valuesDirectory)) {
            //$data['uuid'] = $fieldsData->uuid;
            $data['inputType'] = self::$type;
            $data['name'] = $fieldsData->uuid;
            if(isset($fieldsData->updateData)){
                $data['value'] = $fieldsData->updateData?:[];
            }else {
                $data['value'] = $value?:[];
            }

            $checkDataIter = 0;
            foreach ($fieldsData->valuesDirectory as $item) {
                if(in_array($item['uuid'],$data['value'])){
                    $checkDataIter++;
                }
            }
            if($checkDataIter != count($data['value'])){
                $data['value'] = [];
            }

            $data['disabled'] = false;
            $option = [];
            if (!empty($fieldsData->valuesDirectory)) {
                foreach ($fieldsData->valuesDirectory as $item) {
                    $dataArr['value'] = $item['uuid'];
                    $dataArr['label'] = $item['name'];
                    $dataArr['disabled'] = false;
                    if (!empty($item['img'])) {
                        $dataArr['img'] = config('app.url').Storage::url($item['img']);
                    }
                    if (!empty($item['preview_text'])) {
                        $dataArr['text'] = $item['preview_text'];
                    }
                    if (
                        !empty($item['detail_name']) ||
                        !empty($item['detail_text']) ||
                        !empty($item['detail_img'])
                    ) {
                        if (!empty($item['detail_name'])) {
                            $dataArr['details']['text'] = $item['detail_name'];
                        }
                        if (!empty($item['detail_text'])) {
                            $dataArr['details']['details'] = $item['detail_text'];
                        }
                        if (!empty($item['detail_img'])) {
                            $dataArr['details']['img'] = config('app.url').Storage::url($item['detail_img']);
                        }
                        if (
                            !empty($item['link_text']) &&
                            !empty($item['link']) &&
                            !empty($item['type'])
                        ) {
                            $dataArr['details']['link']['text'] = $item['link_text'];
                            $dataArr['details']['link']['path'] = $item['link'];
                            $dataArr['details']['link']['type'] = $item['type'];
                        }

                    }
                    $option[$item['name']] = $dataArr;
                }
            }
            //ksort($option);
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

