<?php

namespace App\Models\Fields;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
    use HasFactory;

    protected $table = 'fields';
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'parentFields',
        'type',
        'directory',
        'active',
        'step',
        'sort',
        'label',
        'heading',
        'placeholder',
        'dividerTop',
        'dividerBottom',
        'helperInfo',
        'required',
        'helperInfo_text',
        'helperInfo_link',
        'helperInfo_link_text',
        'helperInfo_link_type',
        'drawerInfo_text',
        'drawerInfo_images'
    ];

    public $timestamps = false;
}
