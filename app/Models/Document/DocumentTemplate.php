<?php

namespace App\Models\Document;

use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

/**
 * @property int $id
 * @property string $name
 * @property DocumentTemplatesEnum $type
 * @property float $version
 * @property string $template
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property string $number
 * @property string $place
 */
class DocumentTemplate extends Model
{
    use HasFactory;

    protected $table = 'document_templates';
    protected $fillable = [
        'name',
        'type',
        'version',
        'template',
        'date_start',
        'date_end',
        'number',
        'place',
    ];

    protected $casts = [
        'type' => DocumentTemplatesEnum::class,
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public $timestamps = false;
}
