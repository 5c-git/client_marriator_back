<?php

namespace App\Models\Fields\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $amount
 */
class Reasons extends Model
{
    use HasFactory;

    protected $table = 'directory_reasons';

    protected $fillable = [
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer'
    ];

    public $timestamps = false;
}
