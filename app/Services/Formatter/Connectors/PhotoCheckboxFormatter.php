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
        if(!is_array($value)){
            $value = [$value];
        }
        if(!empty($fieldsData->valuesDirectory)) {
            //$data['uuid'] = $fieldsData->uuid;
            $data['inputType'] = self::$type;
            $data['name'] = $fieldsData->uuid;
            $data['value'] = $value ?: [];
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
                            !empty($item['link_text']) ||
                            !empty($item['link']) ||
                            !empty($item['type'])
                        ) {
                            if (!empty($item['link_text'])) {
                                $dataArr['details']['link']['text'] = $item['link_text'];
                            }
                            if (!empty($item['link'])) {
                                $dataArr['details']['link']['path'] = $item['link'];
                            }
                            if (!empty($item['type'])) {
                                $dataArr['details']['link']['type'] = $item['type'];
                            }
                        }

                    }
                    $option[] = $dataArr;
                }
            }
            $data['options'] = $option;

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
            if (!empty($fieldsData->dividerBotto)) {
                $data['dividerBottom'] = (bool)$fieldsData->dividerBottom;
            }

            if (!empty($fieldsData->helperInfo_text)) {
                $data['helperInfo']['text'] = $fieldsData->helperInfo_text;
            }
            if (!empty($fieldsData->helperInfo_link)) {
                $data['helperInfo']['link']['path'] = $fieldsData->helperInfo_link;
                if (!empty($fieldsData->helperInfo_link_text)) {
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
        }

        return $data;
    }
}

