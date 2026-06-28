<?php

namespace Modules\YandexSmena\Models;

use App\Models\Fields\Directory\ViewActivities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $view_activity_id
 * @property string|null $external_id Yandex-provided profession_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read ViewActivities $viewActivity
 * @property-read \Illuminate\Database\Eloquent\Collection|SmenaShift[] $shifts
 */
class SmenaProfession extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_professions';

    protected $fillable = [
        'view_activity_id',
        'external_id',
        'name',
    ];

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(SmenaShift::class, 'yandex_smena_profession_id');
    }
}
