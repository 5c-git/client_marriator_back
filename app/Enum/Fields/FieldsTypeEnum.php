<?php

namespace App\Enum\Fields;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum FieldsTypeEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case checkbox = 1;
    case checkboxMultiple = 2;
    case file = 3;
    case photoCheckbox = 4;
    case radio = 5;
    case select = 6;
    case text = 7;
    case directory = 8;


    public function typeName(): string
    {
        return match($this)
        {
            self::checkbox => 'Чекбокс',
            self::checkboxMultiple => 'Множественный чекбокс',
            self::file => 'Файл',
            self::photoCheckbox => 'Чекбокс с фото',
            self::radio => 'Радио',
            self::select => 'Список',
            self::text => 'Текст',
            self::directory => 'Справочник',
        };
    }

    public function typeClassFormatter(): string
    {
        return match($this)
        {
            self::checkbox => Connectors\CheckBoxFormatter::class,
            self::checkboxMultiple => Connectors\CheckboxMultipleFormatter::class,
            self::file => Connectors\FileFormatter::class,
            self::photoCheckbox => Connectors\PhotoCheckboxFormatter::class,
            self::radio => Connectors\RadioFormatter::class,
            self::select => Connectors\SelectFormatter::class,
            self::text => Connectors\TextFormatter::class,
        };
    }

    public static function fieldType(){
        return [
            self::text,
            self::checkbox,
            self::file,
            self::directory,
        ];
    }

}
