<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standard extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::select->value;
    public static string $uuid = 'directory_standard';

    protected $table = 'directory_standard';
    protected $fillable = [
        'uuid',
        'name',
        'coefficient',
        'name_doc'
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){

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
                $dataForUpsert[] = ['uuid' => $item['id'], 'name' => $name,'coefficient'=>$item['coefficient']];
            }
            self::truncate();
            self::upsert($dataForUpsert, ['uuid'], ['name','coefficient']);
        }
    }
}
