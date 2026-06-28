<?php

namespace Modules\YandexSmena\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $event_id
 * @property string $event_type
 * @property string $event_ts
 * @property string $direction out|in
 * @property string|null $entity_type
 * @property string|null $entity_id
 * @property array $payload
 * @property array|null $response
 * @property string|null $error
 * @property string|null $source_event_id
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SmenaEventLog extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_event_log';

    protected $fillable = [
        'event_id',
        'event_type',
        'event_ts',
        'direction',
        'entity_type',
        'entity_id',
        'payload',
        'response',
        'error',
        'source_event_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
