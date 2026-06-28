<?php

namespace Modules\YandexSmena\Models;

use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $entity_id Provider-generated shift identifier sent to Yandex
 * @property int|null $order_id
 * @property int|null $task_id
 * @property int|null $order_activity_id
 * @property int|null $task_activity_id
 * @property int $yandex_smena_site_id
 * @property int $yandex_smena_profession_id
 * @property int $yandex_smena_payment_id
 * @property string|null $external_status Yandex-side status (available, assigned, etc.)
 * @property \Carbon\Carbon $start_at
 * @property int $length_min
 * @property int $rest_length_min
 * @property array $payload
 * @property array|null $response
 * @property string|null $sync_error
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $last_poll_at
 * @property string|null $last_source_event_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Order|null $order
 * @property-read Task|null $task
 * @property-read OrderActivities|null $orderActivity
 * @property-read TaskActivity|null $taskActivity
 * @property-read SmenaSite $site
 * @property-read SmenaProfession $profession
 * @property-read SmenaPayment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection|SmenaCandidate[] $candidates
 */
class SmenaShift extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_shifts';

    protected $fillable = [
        'entity_id',
        'order_id',
        'task_id',
        'order_activity_id',
        'task_activity_id',
        'yandex_smena_site_id',
        'yandex_smena_profession_id',
        'yandex_smena_payment_id',
        'external_status',
        'start_at',
        'length_min',
        'rest_length_min',
        'payload',
        'response',
        'sync_error',
        'published_at',
        'last_poll_at',
        'last_source_event_id',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'published_at' => 'datetime',
            'last_poll_at' => 'datetime',
            'payload' => 'array',
            'response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function orderActivity(): BelongsTo
    {
        return $this->belongsTo(OrderActivities::class, 'order_activity_id');
    }

    public function taskActivity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class, 'task_activity_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(SmenaSite::class, 'yandex_smena_site_id');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(SmenaProfession::class, 'yandex_smena_profession_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SmenaPayment::class, 'yandex_smena_payment_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(SmenaCandidate::class, 'yandex_smena_shift_id');
    }
}
