<?php

namespace App\Models\Fields\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'directory_country';
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'parentFields',
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
