<?php
namespace App\Models\Fields\Directory;

use Illuminate\Support\Collection;

interface ModelDirectoryInterface
{
    public function getDataDirectory(bool $allFields = false,array $filterData = []);
    public static function upsertFromImport(array $data): void;
    public static function getDefault(): string|array;
    public static function getAllData(): Collection;
}
