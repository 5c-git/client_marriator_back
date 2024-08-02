<?php

namespace App\Models\Fields\Directory;

use App\Enum\Fields\FieldsTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferSearch extends Model implements ModelDirectoryInterface
{
    use HasFactory;

    public static int $fieldsTypeEnum = FieldsTypeEnum::selectMultiple->value;
    public static string $uuid = 'directory_offer_search';

    protected $table = 'directory_offer_search';
    protected $fillable = [
        'uuid',
        'name',
        'active',
        'parentFields'
    ];

    public $timestamps = false;

    public function getDataDirectory(bool $allFields = false,array $filterData = []){
        $unset = false;
        $parentFields = json_decode($this->parentFields, true);
        if(!empty($parentFields)) {
            foreach ($parentFields as $parentField) {
                $unset = false;
                foreach ($parentField as $oneField) {
                    if (!in_array($oneField, $filterData,true)) {
                        $unset = true;
                    }
                }
                if (!$unset) {
                    break;
                }
            }
        }
        if ($unset) {
            return [];
        }
        if(!$allFields) {
            return $this->uuid;
        }else{
            return $this->toArray();
        }
    }
}
