<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'setting';
    protected $fillable = [
        'key',
        'value',
    ];
    public $timestamps = false;
}
