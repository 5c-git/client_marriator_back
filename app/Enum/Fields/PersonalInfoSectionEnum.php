<?php

namespace App\Enum\Fields;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum PersonalInfoSectionEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case registration = 1;
    case personal = 2;
    case pay = 3;


    public function typeName(): string
    {
        return match($this)
        {
            self::registration => 'Регистрационные данные',
            self::personal => 'Персональные данные',
            self::pay => 'Платежные данные',
        };
    }

    public function sectionSort(): string
    {
        return match($this)
        {
            self::registration => 1,
            self::personal => 2,
            self::pay => 3,
        };
    }

}
