<?php

namespace App\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Fields\Directory\ViewActivities;


/**
 * @property int $bid_id
 * @property int $view_activity_id
 * @property int $count
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property bool $need_foto
 * @property array $date_activity
 * @property-read Bid $bid
 * @property-read ViewActivities $viewActivity
 *
 */
class BidActivity extends Model
{
    use HasFactory;

    protected $table = 'bid_activities';

    protected $fillable = [
        'bid_id',
        'view_activity_id',
        'count',
        'date_start',
        'date_end',
        'need_foto',
        'date_activity'
    ];

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class,'bid_id');
    }

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }
}
