<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Fields\Directory\ModelDirectoryInterface;

class Bank extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::autocomplete->value;
    public static string $uuid = 'directory_bank';

    protected $table = 'directory_bank';
    protected $fillable = [
        'uuid',
        'name',
        'bic',
        'description',
        'parentFields',
        'fields',
        'active',
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
                $code = $item['code'];

                $dataForUpsert[] = ['uuid' => $item['id'], 'name' => $name,'bic'=>$code];
            }
            self::upsert($dataForUpsert, ['uuid'], ['name','bic']);
        }
    }
}
