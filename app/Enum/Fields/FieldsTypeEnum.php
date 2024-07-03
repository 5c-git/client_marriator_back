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
    case account = 9;
    case card = 10;
    case date = 11;
    case email = 12;
    case inn = 13;
    case month = 14;
    case phone = 15;
    case sms = 16;
    case snils = 17;
    case photo = 18;


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
            self::account => 'Лицевой счет',
            self::card => 'Банковская карта',
            self::date => 'Дата',
            self::email => 'Email',
            self::inn => 'ИНН',
            self::month => 'Дата до месяца',
            self::phone => 'Телефон',
            self::sms => 'SMS',
            self::snils => 'Снилс',
            self::photo => 'Фото',
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
            self::account => Connectors\AccountFormatter::class,
            self::card => Connectors\CardFormatter::class,
            self::date => Connectors\DateFormatter::class,
            self::email => Connectors\EmailFormatter::class,
            self::inn => Connectors\InnFormatter::class,
            self::month => Connectors\MonthFormatter::class,
            self::phone => Connectors\PhoneFormatter::class,
            self::sms => Connectors\SmsFormatter::class,
            self::snils => Connectors\SnilsFormatter::class,
            self::photo => Connectors\PhotoFormatter::class,
        };
    }

    public static function fieldType(){
        return [
            self::text,
            self::checkbox,
            self::file,
            self::account,
            self::card,
            self::date,
            self::email,
            self::inn,
            self::month,
            self::phone,
            self::sms,
            self::snils,
            self::directory,
        ];
    }

}
