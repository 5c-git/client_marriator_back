<?php

namespace App\Models\Order;

use App\Enum\Order\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Models\Order\OrderActivities;

/**
 * @property int $id
 * @property int $place_id
 * @property int $user_id
 * @property int $accept_user_id
 * @property int $supervisor_user_id
 * @property int $order_id
 * @property int $task_id
 * @property int $activity_id
 * @property int $view_activity_id
 * @property int $radius
 * @property int $count
 * @property float $price
 * @property bool $self_employed
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property Carbon $created_at
 * @property array $date_activity
 * @property bool $need_foto
 * @property OrderStatusEnum $status
 * @property-read User $user
 * @property-read User $acceptUser
 * @property-read Order $order
 * @property-read Task $task
 * @property-read Place $place
 * @property-read Collection|BidActivity[] $bidActivities
 * @property-read ViewActivities $viewActivity
 * @property-read Collection|User[] $acceptingUsers
 *
 */

class Bid extends Model
{
    use HasFactory;

    protected $table = 'bids';

    protected $fillable = [
        'place_id',
        'user_id',
        'accept_user_id',
        'order_id',
        'task_id',
        'status',
        'self_employed',
        'radius',
        'price',
        'view_activity_id',
        'count',
        'date_start',
        'date_end',
        'need_foto',
        'date_activity',
        'activity_id'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'date_start' => 'datetime',
        'created_at' => 'datetime',
        'date_end' => 'datetime',
        'date_activity' => 'json',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class,'place_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function acceptUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accept_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class,'task_id');
    }

    public function bidActivities(): HasMany
    {
        return $this->hasMany(BidActivity::class);
    }

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }

    public function acceptingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'accept_bid', 'bid_id', 'user_id')
            ->withPivot('accepted','task_id','order_id','user_id_maintainer');
    }
}
