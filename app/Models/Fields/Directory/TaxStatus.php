<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxStatus extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::checkbox->value;

    protected $table = 'directory_tax_status';
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
}
