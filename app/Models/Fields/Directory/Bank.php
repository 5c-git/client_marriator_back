<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Fields\Directory\ModelDirectoryInterface;

class Bank extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static $fieldsTypeEnum = FieldsTypeEnum::text->value;

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

    public function getDataDirectory(bool $allFields = false){
        if(!$allFields) {
            return $this->uuid;
        }else{
            return $this->toArray();
        }
    }
}
