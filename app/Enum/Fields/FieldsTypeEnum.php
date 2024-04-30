<?php

namespace App\Enum\Fields;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum FieldsTypeEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case int = 1;
    case string = 2;
    case select = 3;
    case directory = 4;


    public function typeName(): string
    {
        return match($this)
        {
            self::int => 'Число',
            self::string => 'Строка',
            self::select => 'Список',
            self::directory => 'Справочник',
        };
    }

}
