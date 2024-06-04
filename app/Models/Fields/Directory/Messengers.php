<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messengers extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static $fieldsTypeEnum = FieldsTypeEnum::select->value;

    protected $table = 'directory_messengers';
    protected $fillable = [
        'uuid',
        'name',
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

    public function getDirectoryFields($value){
        return false;
    }
}
