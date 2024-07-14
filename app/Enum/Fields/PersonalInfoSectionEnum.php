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

    case personal = 1;
    case activities = 2;
    case certificates = 3;
    case pay = 4;
    case searchRadius = 5;
    case property = 6;
    case documents = 7;


    public function typeName(): string
    {
        return match($this)
        {
            self::personal => 'Персональные данные',
            self::activities => 'Виды деятельности',
            self::certificates => 'Допуски, справки, удостоверения',
            self::pay => 'Платежные документы',
            self::searchRadius => 'Радиус поиска работы',
            self::property => 'Имущество',
            self::documents => 'Документы иностранного гражданина',
        };
    }

    public function sectionSort(): string
    {
        return match($this)
        {
            self::personal => 1,
            self::activities => 2,
            self::certificates => 3,
            self::pay => 4,
            self::searchRadius => 5,
            self::property => 6,
            self::documents => 7,
        };
    }

}
