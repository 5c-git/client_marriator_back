<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Project;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $address_kladr
 * @property float $latitude
 * @property float $longitude
 * @property-read Collection|Brand[] $brand
 * @property-read Collection|Project[] $project
 *
 */
class Place extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_place';

    protected $table = 'directory_place';
    protected $fillable = [
        'uuid',
        'name',
        'brand_id',
        'address_kladr',
        'latitude',
        'longitude',
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){

    }

    public static function upsertFromImport(array $data): void
    {

    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function project(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'directory_project_directory_place',
            'place_id',
            'project_id'
        );
    }
}
