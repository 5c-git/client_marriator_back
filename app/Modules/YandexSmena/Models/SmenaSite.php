<?php

namespace Modules\YandexSmena\Models;

use App\Models\Fields\Directory\Place;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $place_id
 * @property string|null $external_id Yandex-provided site_id
 * @property string $name
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Place $place
 * @property-read \Illuminate\Database\Eloquent\Collection|SmenaShift[] $shifts
 */
class SmenaSite extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_sites';

    protected $fillable = [
        'place_id',
        'external_id',
        'name',
        'address',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(SmenaShift::class, 'yandex_smena_site_id');
    }
}
