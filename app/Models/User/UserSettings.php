<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property boolean $notification_new_bids
 *
 */
class UserSettings extends Model
{
    use HasFactory;

    protected $table = 'user_settings';
    protected $fillable = [
        'user_id',
        'notification_new_bids'
    ];

    protected $casts = [
    ];
}
