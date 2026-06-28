<?php

namespace Modules\YandexSmena\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $last_event_id
 * @property \Carbon\Carbon|null $polled_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SmenaPollState extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_poll_state';

    protected $fillable = [
        'last_event_id',
        'polled_at',
    ];

    protected function casts(): array
    {
        return [
            'polled_at' => 'datetime',
        ];
    }

    public static function cursor(): ?string
    {
        $state = self::query()->first();

        return $state?->last_event_id;
    }

    public static function updateCursor(string $lastEventId): void
    {
        self::query()->updateOrCreate(
            ['id' => 1],
            [
                'last_event_id' => $lastEventId,
                'polled_at' => now(),
            ]
        );
    }
}
