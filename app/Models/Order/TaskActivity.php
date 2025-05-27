<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Fields\Directory\ViewActivities;

class TaskActivity extends Model
{
    use HasFactory;

    protected $table = 'task_activities';

    protected $fillable = [
        'task_id',
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

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }
}
