<?php

namespace App\Models\Fields;

use App\Models\Fields\Directory\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User\Role;

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
        'drawerInfo_images',
        'section',
        'estate',
        'requisites',
        'screen',
        'role_id',
        'default_value',
        'preg_value',
        'preg_text',
    ];

    public $timestamps = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'fields_user_role',
            'field_id',
            'user_role_id'
        );
    }

}
