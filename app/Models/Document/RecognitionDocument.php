<?php

namespace App\Models\Document;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\RecognitionDocumentStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $link
 * @property RecognitionDocumentStatusEnum $status
 * @property array|null $data
 * @property int $user_id
 * @property string $file_field
 * @property int $external_package_id
 * @property int $activity_id
 * @property-read User $user
 *
 */
class RecognitionDocument extends Model
{
    use HasFactory;

    protected $table = 'recognition_documents';
    protected $fillable = [
        'link',
        'status',
        'data',
        'user_id',
        'file_field',
        'external_package_id',
    ];

    protected $casts = [
        'data' => 'json',
        'status' => RecognitionDocumentStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
