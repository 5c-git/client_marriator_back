<?php

namespace App\Models\Order;

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
use App\Enum\Order\OrderStatusEnum;


/**
 * @property int $id
 * @property int $place_id
 * @property int $user_id
 * @property int $accept_user_id
 * @property int $specialist_user_id
 * @property int $order_id
 * @property int $project_id
 * @property int $scope_of_services
 * @property float $price
 * @property float $income
 * @property bool $self_employed
 * @property OrderStatusEnum $status
 * @property-read User $user
 * @property-read User $acceptUser
 * @property-read User $specialistUser
 * @property-read Order $order
 * @property-read Bid $bid
 * @property-read Place $place
 * @property-read Project $project
 * @property-read Collection|TaskActivity[] $taskActivities
 * @property-read Collection|ViewActivities[] $viewActivities
 * @property-read Collection|User[] $acceptingUsers
 *
 */
class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'place_id',
        'user_id',
        'accept_user_id',
        'specialist_user_id',
        'order_id',
        'status',
        'self_employed',
        'price',
        'income',
        'scope_of_services',
        'project_id'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class,'place_id');
    }
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function acceptUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accept_user_id');
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'bid_id');
    }

    public function specialistUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specialist_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function taskActivities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function viewActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ViewActivities::class,
            'task_activities',
            'task_id',
            'view_activity_id'
        )->withPivot(['count', 'date_start', 'date_end', 'need_foto', 'date_activity']);
    }

    public function acceptingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'accept_task', 'task_id', 'user_id')
            ->withPivot('accepted');
    }
}
