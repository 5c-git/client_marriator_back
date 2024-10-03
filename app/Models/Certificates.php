<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

class Certificates extends Model
{
    use HasFactory;

    protected $table = 'certificates';
    protected $fillable = [
        'key',
        'value',
    ];
    public $timestamps = false;
}
