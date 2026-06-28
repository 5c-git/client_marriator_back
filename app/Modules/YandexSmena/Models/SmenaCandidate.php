<?php

namespace Modules\YandexSmena\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $yandex_smena_shift_id
 * @property string $external_worker_id
 * @property string $status pending|approved|rejected|withdrawn
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $phone
 * @property string|null $inn
 * @property string|null $snils
 * @property array $raw_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read SmenaShift $shift
 */
class SmenaCandidate extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_candidates';

    protected $fillable = [
        'yandex_smena_shift_id',
        'external_worker_id',
        'status',
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'inn',
        'snils',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(SmenaShift::class, 'yandex_smena_shift_id');
    }
}
