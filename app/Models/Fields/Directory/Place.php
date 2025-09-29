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
use App\Models\Fields\Directory\RegionOfResidence;

/**
 * @property int $id
 * @property int $directory_region_of_residence_id
 * @property int $verme_id
 * @property string $uuid
 * @property string $name
 * @property string $address_kladr
 * @property float $latitude
 * @property float $longitude
 * @property-read Collection|Project[] $project
 * @property-read RegionOfResidence $region
 *
 */
class Place extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_place';
    public static string $nameCustom = 'Место проведения';


    protected $table = 'directory_place';
    protected $fillable = [
        'uuid',
        'name',
        'address_kladr',
        'latitude',
        'longitude',
        'directory_region_of_residence_id',
        'verme_id'
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){

    }

    public static function upsertFromImport(array $data): void
    {

    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(RegionOfResidence::class, 'directory_region_of_residence_id');
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
