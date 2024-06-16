<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activities extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;

    protected $table = 'directory_activities';
    protected $fillable = [
        'uuid',
        'name',
        'img',
        'active',
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false){
        if(!$allFields) {
            return $this->uuid;
        }else{
            return $this->toArray();
        }
    }
}
