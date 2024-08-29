<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activities extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_activities';

    protected $table = 'directory_activities';
    protected $fillable = [
        'uuid',
        'name',
        'img',
        'active',
        'preview_text',
        'detail_name',
        'detail_text',
        'detail_img',
        'link_text',
        'link',
        'type',
        'parentFields'
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){
        $unset = false;
        $parentFields = json_decode($this->parentFields, true);
        if(!empty($parentFields)) {
            foreach ($parentFields as $parentField) {
                $unset = false;
                foreach ($parentField as $oneField) {
                    if (!in_array($oneField, $filterData,true)) {
                        $unset = true;
                    }
                }
                if (!$unset) {
                    break;
                }
            }
        }
        if ($unset) {
            return [];
        }
        if(!$allFields) {
            return $this->uuid;
        }else{
            return $this->toArray();
        }
    }

    public static function upsertFromImport(array $data): void
    {
        foreach (array_chunk($data,1000) as $dataChunk) {
            $dataForUpsert = [];
            foreach ($dataChunk as $item) {
                $name = $item['name'];
                if (empty($name)) {
                    $name = $item['code'];
                }
                $dataForUpsert[] = ['uuid' => $item['id'], 'name' => $name,'active'=>true];
            }
            self::upsert($dataForUpsert, ['uuid'], ['name','active']);
        }
    }
}
