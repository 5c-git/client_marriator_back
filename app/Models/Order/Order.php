<?php

namespace App\Models\Order;

use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use App\Models\Order\OrderActivities;
use App\Enum\Order\OrderStatusEnum;
use App\Models\Order\Task;

/**
 * @property int $id
 * @property int $place_id
 * @property int $user_id
 * @property int $accept_user_id
 * @property string $external_id
 * @property int $external_type
 * @property bool $self_employed
 * @property OrderStatusEnum $status
 * @property-read User $user
 * @property-read Place $place
 * @property-read Collection|OrderActivities[] $orderActivities
 * @property-read Collection|ViewActivities[] $viewActivities
 * @property-read Collection|User[] $acceptingUsers
 * @property-read User $acceptUser
 * @property-read Collection|User[] $acceptOrder
 * @property-read Collection|Task[] $tasks
 * @property-read Collection|Bid[] $bids
 *
 */
class Order extends Model implements OrderInterface
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'place_id',
        'project_id',
        'user_id',
        'self_employed',
        'status',
        'accept_user_id',
        'external_id',
        'external_type'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function acceptUser(): BelongsTo
    {
        return $this->belongsTo(User::class,'accept_user_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function orderActivities(): HasMany
    {
        return $this->hasMany(OrderActivities::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function acceptingUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'accept_order',
            'order_id',
            'user_id'
        );
    }

    public function viewActivities()
    {
        return $this->belongsToMany(
            ViewActivities::class,
            'order_activities',
            'order_id',
            'view_activity_id'
        )->withPivot(['count', 'date_start', 'date_end', 'need_foto', 'date_activity']);
    }

}
