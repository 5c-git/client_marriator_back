<?php

namespace App\Models\Document;

use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
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
    ];

    protected $casts = [
        'type' => DocumentTemplatesEnum::class,
    ];

    public $timestamps = false;
}
