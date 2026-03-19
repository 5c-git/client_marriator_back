<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\Fields\Directory\Counterparty;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $logo
 * @property string $external_id
 * @property string $description
 * @property-read Collection|Counterparty[] $counterparties
 *
 */
class Brand extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_brand';

    protected $table = 'directory_brand';
    protected $fillable = [
        'uuid',
        'name',
        'logo',
        'description',
        'external_id',
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){

    }

    public static function upsertFromImport(array $data): void
    {

    }

    public function counterparties(): BelongsToMany
    {
        return $this->belongsToMany(
            Counterparty::class,
            'directory_brand_directory_counterparty',
            'brand_id',
            'counterparty_id'
        );
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Counterparty::class,
            'directory_project_directory_brand',
            'brand_id',
            'project_id'
        );
    }

    public static function getDefault(): string|array
    {
        return '';
    }
}
