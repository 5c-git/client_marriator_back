<?php

namespace App\Models\Fields\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reasons extends Model
{
    use HasFactory;

    protected $table = 'directory_reasons';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
