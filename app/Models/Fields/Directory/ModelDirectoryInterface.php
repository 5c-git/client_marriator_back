<?php
namespace App\Models\Fields\Directory;

interface ModelDirectoryInterface
{
    public function getDataDirectory(bool $allFields = false,array $filterData = []);

}
