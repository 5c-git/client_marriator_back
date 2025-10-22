<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    protected $fillable = [
        'uuid',
        'user_id',
        'file_path',
        'file_name',
        'status',
        'status_signature',
        'date_signature',
        'document_id',
        'file_id',
        'file_path_signed'
    ];

    protected $casts = [
        'status' => DocumentStatusEnum::class,
        'status_signature' => DocumentStatusSignatureEnum::class,
        'date_signature' => 'datetime'
    ];

    public $timestamps = false;
}
