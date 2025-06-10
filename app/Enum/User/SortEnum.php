<?php

namespace App\Enum\User;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;

enum SortEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case new = 1;
    case old = 2;
    case all = 3;

}
