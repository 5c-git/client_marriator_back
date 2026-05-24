<?php

namespace App\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Fields\Directory\ViewActivities;


/**
 * @property int $task_id
 * @property int $view_activity_id
 * @property int $count
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property bool $need_foto
 * @property array $date_activity
 * @property-read Task $task
 * @property-read Task $bidOrTask
 * @property-read ViewActivities $viewActivity
 *
 */
class TaskActivity extends Model
{
    use HasFactory;

    protected $table = 'task_activities';
    public $timestamps = false;
    protected $fillable = [
        'task_id',
        'order_activity_id',
        'view_activity_id',
        'count',
        'date_start',
        'date_end',
        'need_foto',
        'date_activity'
    ];

    protected $casts = [
        'date_activity' => 'json',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class,'task_id');
    }

    public function bidOrTask(): BelongsTo
    {
        return $this->belongsTo(Task::class,'task_id');
    }

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }
}
