<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

/**
 * @property string $key
 * @property string $value
 * @property string $name
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $fillable = [
        'key',
        'value',
        'name'
    ];
    public $timestamps = false;

    public static function getValue(string $key): ?string
    {
        $setting = self::query()->select('value')->where('key',$key)->first();
        return $setting?->value ?? null;
    }
}
