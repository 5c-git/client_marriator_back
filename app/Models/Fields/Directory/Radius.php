<?php

namespace App\Models\Fields\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Radius extends Model
{
    use HasFactory;

    protected $table = 'directory_radius';
    public static string $uuid = 'directory_radius';

    protected $fillable = [
        'uuid',
        'value',
        'default',
    ];

    public $timestamps = false;

    public static function getDefaultValue()
    {
       $radius = self::where('default',true)->first();
       if($radius){
           return $radius->value;
       }
       return 5;
    }

    public static function getDefault(): string|array
    {
        return '';
    }

    public static function getAllData(): Collection
    {
        return self::query()->get();
    }
}
