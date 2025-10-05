<?php

namespace App\Enum\Document\DocumentFieldRecognition;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum Passport: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case BirthDate = 1;
    case BirthPlace = 2;
    case FirstName = 3;
    case GivenBy = 4;
    case GivenDate = 5;
    case LastName = 6;
    case MiddleName = 7;
    case Number = 8;
    case Series = 9;
    case Sex = 10;
    case SubdivisionCode = 11;

    public function getUserBinding(): string
    {
        return match($this)
        {
            self::BirthDate => 'Дата рождения',
            self::BirthPlace => 'Место рождения',
            self::FirstName => 'Имя',
            self::GivenBy => 'Кем выдан',
            self::GivenDate => 'Когда выдан',
            self::LastName => 'Фамилия',
            self::MiddleName => 'Отчество',
            self::Number => 'Номер паспорта',
            self::Series => 'Серия паспорта',
            self::Sex => 'Пол',
            self::SubdivisionCode => 'Подразделение миграции',
        };
    }
}
