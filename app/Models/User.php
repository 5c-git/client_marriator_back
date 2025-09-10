<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use App\Observers\UserObserver;
use App\Models\Order\Order;


/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property-read Collection|Project[] $project
 * @property-read Collection|Place[] $place
 * @property-read Collection|Order[] $acceptedOrders
 * @property-read Collection|Order[] $acceptOrders
 * @property-read Collection|Task[] $acceptedTasks
 * @property-read Collection|Task[] $acceptTasks
 * @property-read Collection|Bid[] $acceptedBids
 * @property-read Collection|Bid[] $acceptBids
 * @property-read Collection|User[] $supervisors
 * @property-read Collection|User[] $managerSpecialist
 * @property-read Collection|User[] $supervisorSpecialist
 * @property-read Collection|Counterparty[] $counterparty
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'phone',
        'data',
        'img',
        'confirmRegister',
        'pin',
        'finishRegister',
        'expansionData',
        'errorData',
        'estateData',
        'requisitesData',
        'mapAddress',
        'mapRadius',
        'updateData',
        'change_fields',
        'date_for_send',
        'uuid',
        'register_hash',
        'change_order',
        'cancel_order',
        'live_order',
        'change_task',
        'cancel_task',
        'live_task',
        'repeat_bid',
        'leave_bid',
        'refusal_task',
        'waiting_task',
        'latitude',
        'longitude',
        'count_wait_bid',
        'time_answer_bid',
        'notification_start'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function acceptOrder(): HasMany
    {
        return $this->hasMany(Order::class,'accept_user_id');
    }

    public function acceptBid(): HasMany
    {
        return $this->hasMany(Bid::class,'accept_user_id');
    }

    public function acceptTask(): HasMany
    {
        return $this->hasMany(Task::class,'accept_user_id');
    }

    public function isAdmin()
    {
        return $this->roles()->where('name', 'admin')->exists();
    }

    public function project(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'user_directory_project',
            'user_id',
            'project_id'
        );
    }

    public function counterparty(): BelongsToMany
    {
        return $this->belongsToMany(
            Counterparty::class,
            'user_directory_counterparty',
            'user_id',
            'counterparty_id'
        );
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'manager_supervisor',
            'user_id_manager',
            'user_id_supervisor'
        );
    }

    public function manager(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'manager_supervisor',
            'user_id_supervisor',
            'user_id_manager'
        );
    }

    public function managerSpecialist(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'manager_specialist',
            'user_id_manager',
            'user_id_specialist'
        );
    }

    public function supervisorSpecialist(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'supervisor_specialist',
            'user_id_supervisor',
            'user_id_specialist'
        );
    }

    public function acceptedOrders(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            'accept_order',
            'user_id',
            'order_id'
        );
    }

    public function place(): BelongsToMany
    {
        return $this->belongsToMany(
            Place::class,
            'user_directory_place',
            'user_id',
            'place_id'
        );
    }

    public function generateToken()
    {
        $this->api_token =  Str::random(60);
        $this->save();

        return $this->api_token;
    }

    static function checkToken($api_token)
    {
        $user = User::where('api_token','=',$api_token)->first();
        if($user) {
            Auth::login($user, true);
            return $user;
        }else{
            return false;
        }
    }

    public function acceptedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'accept_task', 'user_id', 'task_id')
            ->withPivot('accepted');
    }

    public function acceptedBids(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'accept_bid', 'user_id', 'bid_id')
            ->withPivot('accepted');
    }
}
