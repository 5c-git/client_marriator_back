<?php

namespace App\Enum\Wfm;
use App\Models\Fields\Directory\Counterparty;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;

enum WfmTypeEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case timeBook = 1;
    case x5 = 2;
    case verme = 3;

    public static function fromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        return null;
    }
}
