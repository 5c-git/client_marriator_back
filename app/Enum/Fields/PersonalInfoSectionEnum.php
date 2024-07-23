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
    case certificates = 2;
    case searchRadius = 3;
    case documents = 4;


    public function typeName(): string
    {
        return match($this)
        {
            self::personal => 'Персональные данные',
            self::certificates => 'Допуски, справки, удостоверения',
            self::searchRadius => 'Радиус поиска работы',
            self::documents => 'Документы иностранного гражданина',
        };
    }

    public function getType(): string
    {
        return match($this)
        {
            self::personal => 'default',
            self::certificates => 'default',
            self::searchRadius => 'default',
            self::documents => 'default',
        };
    }

    public function sectionSort(): string
    {
        return match($this)
        {
            self::personal => 1,
            self::certificates => 2,
            self::searchRadius => 3,
            self::documents => 4,
        };
    }

    public function filter($query):string
    {
        return match($this)
        {
            self::personal => $query->where('section',1),
            self::certificates => $query->where('section',2),
            self::searchRadius => $query->where('section',3),
            self::documents => $query->where('section',4),
        };
    }

    public function getField(){
        return match($this)
        {
            self::personal => false,
            self::certificates => false,
            self::searchRadius => false,
            self::documents => false,
        };
    }


}
