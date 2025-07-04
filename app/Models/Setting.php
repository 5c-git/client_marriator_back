<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

/**
 * @property string $key
 * @property string $value
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $fillable = [
        'key',
        'value',
    ];
    public $timestamps = false;
}
