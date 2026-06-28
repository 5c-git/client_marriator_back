<?php

namespace Modules\YandexSmena\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code Local tariff code
 * @property string|null $external_id Yandex-provided payment_id
 * @property string $name
 * @property int|null $amount_per_hour
 * @property string $currency
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|SmenaShift[] $shifts
 */
class SmenaPayment extends Model
{
    use HasFactory;

    protected $table = 'yandex_smena_payments';

    protected $fillable = [
        'code',
        'external_id',
        'name',
        'amount_per_hour',
        'currency',
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(SmenaShift::class, 'yandex_smena_payment_id');
    }
}
