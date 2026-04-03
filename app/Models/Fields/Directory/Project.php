<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property string $external_id
 * @property-read Collection|Counterparty[] $counterparties
 * @property-read Collection|Place[] $places
 * @property-read Collection|ViewActivities[] $viewActivities
 * @property-read Collection|Brand[] $brands
 *
 */
class Project extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_project';
    public static string $nameCustom = 'Проект';

    protected $table = 'directory_project';
    protected $fillable = [
        'uuid',
        'name',
        'date_start',
        'date_end',
        'external_id'
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
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
            'directory_project_directory_counterparty',
            'project_id',
            'counterparty_id'
        );
    }

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(
            Place::class,
            'directory_project_directory_place',
            'project_id',
            'place_id'
        );
    }

    public function viewActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ViewActivities::class,
            'directory_project_directory_view_activities',
            'project_id',
            'view_activities_id'
        )->withPivot('price');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(
            Brand::class,
            'directory_project_directory_brand',
            'project_id',
            'brand_id'
        );
    }

    static function getForUserQr()
    {
        return self::query()->where('date_start', '<=', Carbon::now())
            ->where('date_end', '>=', Carbon::now())->get()->toArray();
    }

    public static function getDefault(): string|array
    {
        return '';
    }

    public static function getAllData(): Collection
    {
        return self::query()->get();
    }
}
