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

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $inn
 * @property string $ogrn
 * @property string $legal_address
 * @property string $legal_email
 * @property string $position
 * @property string $full_name
 * @property string $brand_name
 * @property string $bank_name
 * @property string $bank_corr_account
 * @property string $bank_bic
 * @property string $okpo
 * @property string $okved
 * @property string $phone
 * @property string $web
 * @property string $bank_account_number
 * @property string $kpp
 * @property-read Collection|Brand[] $brands
 * @property-read Collection|Project[] $projects
 *
 */
class Counterparty extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;
    public static string $uuid = 'directory_counterparty';
    public static string $nameCustom = 'Контрагент';


    protected $table = 'directory_counterparty';
    protected $fillable = [
        'uuid',
        'name',
        'inn',
        'ogrn',
        'legal_address',
        'legal_email',
        'position',
        'full_name',
        'brand_name',
        'kpp',
        'bank_name',
        'bank_account_number',
        'bank_corr_account',
        'bank_bic',
        'okpo',
        'okved',
        'phone',
        'web',
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){

    }

    public static function upsertFromImport(array $data): void
    {

    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(
            Brand::class,
            'directory_brand_directory_counterparty',
            'counterparty_id',
            'brand_id'
        );
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'directory_project_directory_counterparty',
            'counterparty_id',
            'project_id'
        );
    }

    static function getForUserQr()
    {
        return self::query()->get()->toArray();
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
