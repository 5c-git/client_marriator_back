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
}
