<?php

namespace App\Enum\User;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;

enum UserStatusModerationEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case new = 1;
    case inProgress = 2;
    case archive = 3;

}
