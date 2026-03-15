<?php

namespace App\Models\Wfm;

use App\Enum\Wfm\WfmTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $externalId
 * @property string $name
 * @property WfmTypeEnum $type
 */
class WfmViewActivities extends Model
{
    use HasFactory;

    protected $table = 'wfm_view_activities';
    protected $fillable = [
        'externalId',
        'name',
        'type',
    ];

    protected $casts = [
        'type' => WfmTypeEnum::class,
    ];

    public $timestamps = false;
}
