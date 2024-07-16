<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::select->value;
    public static string $uuid = 'directory_documentation';

    protected $table = 'directory_documentation';
    protected $fillable = [
        'uuid',
        'name',
        'active',
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
                    if (!in_array($oneField, $filterData)) {
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
}
