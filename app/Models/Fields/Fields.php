<?php

namespace App\Models\Fields;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
    use HasFactory;

    protected $table = 'fields';
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'parentFields',
        'type',
        'directory',
        'active',
        'step'
    ];

    public $timestamps = false;
}
