<?php
namespace App\Models\Order;

use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use App\Models\Fields\Directory\ViewActivities;

/**
 * @property int $id
 * @property int $order_id
 * @property int $view_activity_id
 * @property int $count
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property bool $need_foto
 * @property array $date_activity
 * @property-read Order $order
 * @property-read ViewActivities $viewActivity
 *
 */
class OrderActivities extends Model
{
    use HasFactory;

    protected $table = 'order_activities';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'view_activity_id',
        'count',
        'date_start',
        'date_end',
        'need_foto',
        'date_activity',
    ];

    protected $casts = [
        'date_activity' => 'json',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function viewActivity(): BelongsTo
    {
        return $this->belongsTo(ViewActivities::class, 'view_activity_id');
    }
}
