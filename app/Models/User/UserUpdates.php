<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;

class UserUpdates extends Model
{
    use HasFactory;

    protected $table = 'user_updates';
    protected $fillable = [
        'user_id',
        'field',
        'newData',
        'oldData',
        'status',
    ];
    public $timestamps = false;
}
