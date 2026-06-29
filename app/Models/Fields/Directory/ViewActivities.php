<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class ViewActivities extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::photoCheckbox->value;

    public static string $uuid = 'directory_view_activities';

    protected $table = 'directory_view_activities';

    protected $fillable = [
        'uuid',
        'name',
        'img',
        'active',
        'preview_text',
        'detail_name',
        'detail_text',
        'detail_img',
        'link_text',
        'link',
        'type',
        'parentFields',
        'self_employed',
        'traveling',
        'external_id_verme',
        'external_id_x5',
        'external_id_timeBook',
        'standard',
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false, array $filterData = [])
    {
        $unset = false;
        $parentFields = json_decode($this->parentFields, true);
        if (! empty($parentFields)) {
            foreach ($parentFields as $parentField) {
                $unset = false;
                foreach ($parentField as $oneField) {
                    if (! in_array($oneField, $filterData, true)) {
                        $unset = true;
                    }
                }
                if (! $unset) {
                    break;
                }
            }
        }
        if ($unset) {
            return [];
        }
        if (! $allFields) {
            return $this->uuid;
        } else {
            return $this->toArray();
        }
    }

    public static function upsertFromImport(array $data): void
    {
        foreach (array_chunk($data, 1000) as $dataChunk) {
            $dataForUpsert = [];
            foreach ($dataChunk as $item) {
                $name = $item['name'];
                if (empty($name)) {
                    $name = $item['code'];
                }
                $dataForUpsert[] = ['uuid' => $item['id'], 'name' => $name, 'active' => true];
            }
            self::truncate();
            self::upsert($dataForUpsert, ['uuid'], ['name', 'active']);
        }
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'directory_project_directory_view_activities',
            'view_activities_id',
            'project_id'
        )->withPivot('price');
    }

    public function belongsViewActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ViewActivities::class,
            'view_activities_view_activities',
            'view_activities_one',
            'view_activities_two'
        );
    }

    public function linkedViewActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ViewActivities::class,
            'view_activities_view_activities',
            'view_activities_two',
            'view_activities_one'
        );
    }

    public function getAllConnectedUuids(): array
    {
        return $this->belongsViewActivities
            ->merge($this->linkedViewActivities)
            ->pluck('uuid')
            ->unique()
            ->values()
            ->toArray();
    }

    public function standardDirectory(): BelongsTo
    {
        return $this->belongsTo(Standard::class, 'standard', 'uuid');
    }

    public static function getDefault(): string|array
    {
        return '';
    }

    public static function getAllData(): Collection
    {
        return self::query()->where('active', true)->get();
    }
}
