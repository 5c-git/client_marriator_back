<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Phone extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::text->value;
    public static string $uuid = 'directory_phone';

    protected $table = 'directory_phone';
    protected $fillable = [
        'uuid',
        'name',
        'active'
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){
        if(!$allFields) {
            return $this->uuid;
        }else{
            return $this->toArray();
        }
    }

    public static function upsertFromImport(array $data): void
    {
        foreach (array_chunk($data,1000) as $dataChunk) {
            $dataForUpsert = [];
            foreach ($dataChunk as $item) {
                $name = $item['name'];
                if (empty($name)) {
                    $name = $item['code'];
                }
                $dataForUpsert[] = ['uuid' => $item['id'], 'name' => $name];
            }
            self::truncate();
            self::upsert($dataForUpsert, ['uuid'], ['name']);
        }
    }

    public static function getDefault(): string|array
    {
        $phone = self::query()->where('active', true)->inRandomOrder()
            ->first();
        if (!empty($phone)) {
            return $phone->name;
        }
        return '';
    }

    public static function getAllData(): Collection
    {
        return self::query()->where('active',true)->get();
    }
}
