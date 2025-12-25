<?php
namespace App\Models\Order;

use App\Enum\Order\ReportStatusEnum;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Reasons;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use App\Models\Fields\Directory\ViewActivities;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $user_id
 * @property int $bid_id
 * @property int $order_id
 * @property int $task_id
 * @property float $coefficient
 * @property float $hours
 * @property int|null $dayActivity
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property Carbon $date_auto_close
 * @property ReportStatusEnum $status
 * @property array $report
 * @property Order $order
 * @property Task $task
 * @property Bid $bid
 * @property bool $pvp
 * @property User $user
 * @property float $forPay
 * @property float $income
 * @property Reasons $reasons
 * @property string $placeholder
 *
 */
class Report extends Model
{
    use HasFactory;

    protected $table = 'report';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'bid_id',
        'order_id',
        'task_id',
        'date_start',
        'date_end',
        'status',
        'report',
        'date_auto_close',
        'dayActivity',
        'forPay',
        'income',
        'coefficient',
        'hours',
        'placeholder',
        'pvp'
    ];

    protected $casts = [
        'status' => ReportStatusEnum::class,
        'report' => 'json',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'date_auto_close' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class,'task_id');
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class,'bid_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function reasons(): BelongsToMany
    {
        return $this->belongsToMany(Reasons::class, 'report_reason', 'report_id', 'reason_id')
            ->withPivot('count');
    }

    public function getReasonsAmount(): int
    {
        return (int) ($this->reasons()->getQuery()->sum(
            DB::raw('directory_reasons.amount * report_reason.count')
        ) ?? 0);
    }

}
