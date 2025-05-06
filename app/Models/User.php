<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use App\Observers\UserObserver;


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
        'coordinates',
        'change_fields',
        'date_for_send',
        'uuid',
        'register_hash'
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
}
