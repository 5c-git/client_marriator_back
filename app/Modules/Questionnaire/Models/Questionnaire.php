<?php

namespace Modules\Questionnaire\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Questionnaire\Enums\QuestionnaireStatus;
use Modules\Questionnaire\Services\QuestionnaireDataMapper;

/**
 * @property int $id
 * @property int $user_id
 * @property QuestionnaireStatus|string $status
 * @property int|null $current_step_index
 * @property string|null $current_step_class
 * @property array $data
 * @property array|null $expansion_data
 * @property array|null $error_data
 * @property array|null $requisites_data
 * @property array|null $logs
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $failed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 *
 * The data array contains:
 * - base user fields (user_id, name, email, phone, img, finishRegister, confirmRegister, pin, uuid, latitude, longitude, mapAddress, mapRadius)
 * - all keys from users.data (flat or step-based)
 * - registration_fields metadata
 * - derived/enriched keys written by steps
 *
 * Descriptive user data lives in separate JSON columns:
 * - expansion_data
 * - error_data
 * - requisites_data
 */
class Questionnaire extends Model
{
    use HasFactory;

    protected $table = 'questionnaires';

    protected $fillable = [
        'user_id',
        'status',
        'current_step_index',
        'current_step_class',
        'data',
        'expansion_data',
        'error_data',
        'requisites_data',
        'logs',
        'error_message',
        'completed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'expansion_data' => 'array',
            'error_data' => 'array',
            'requisites_data' => 'array',
            'logs' => 'array',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return a normalized copy of data with semantic keys added.
     * Original data (UUID keys) stays unchanged in the database.
     */
    public function mappedData(): array
    {
        return app(QuestionnaireDataMapper::class)->map($this->data ?? []);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            QuestionnaireStatus::COMPLETED->value,
            QuestionnaireStatus::FAILED->value,
        ], true);
    }

    public function markInProgress(int $stepIndex, string $stepClass): void
    {
        $this->status = QuestionnaireStatus::IN_PROGRESS->value;
        $this->current_step_index = $stepIndex;
        $this->current_step_class = $stepClass;
        $this->error_message = null;
        $this->save();
    }

    public function markCompleted(): void
    {
        $this->status = QuestionnaireStatus::COMPLETED->value;
        $this->current_step_index = null;
        $this->current_step_class = null;
        $this->error_message = null;
        $this->completed_at = now();
        $this->save();
    }

    public function markFailed(int $stepIndex, string $stepClass, string $message): void
    {
        $this->status = QuestionnaireStatus::FAILED->value;
        $this->current_step_index = $stepIndex;
        $this->current_step_class = $stepClass;
        $this->error_message = $message;
        $this->failed_at = now();
        $this->save();
    }

    public function appendLog(array $entry): void
    {
        $logs = $this->logs ?? [];
        $logs[] = $entry;
        $this->logs = $logs;
    }
}
