<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $counterparty_id
 * @property array $data
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class UserContractData extends Model
{
    use HasFactory;

    protected $table = 'user_contract_data';
    protected $fillable = [
        'user_id',
        'counterparty_id',
        'data',
        'date_start',
        'date_end',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data' => 'json',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
