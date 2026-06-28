<?php

namespace Modules\YandexSmena\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $external_worker_id
 * @property int|null $yandex_smena_site_id
 * @property int|null $yandex_smena_profession_id
 * @property bool $is_favorite
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SmenaSite|null $site
 * @property-read SmenaProfession|null $profession
 */
class SmenaFavoriteWorker extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_favorite_workers';

    protected $fillable = [
        'external_worker_id',
        'yandex_smena_site_id',
        'yandex_smena_profession_id',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(SmenaSite::class, 'yandex_smena_site_id');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(SmenaProfession::class, 'yandex_smena_profession_id');
    }
}
