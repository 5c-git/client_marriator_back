<?php

namespace App\Models\Fields\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

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
        return false;
    }

    public function getDirectoryFields($value){
        if($this->bic == $value){
            return json_decode($this->fields,true);
        }
        return false;
    }
}
