<?php

namespace App\Enum\Document;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentFiledRecognition: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case docType = 'DocType';
    case number = 'Number';
    case series = 'Series';
    case givenBy = 'GivenBy';
    case passportId = 'PassportId';

    public function getUserBinding(): string
    {
        return match($this)
        {
            self::docType => 'Тип документа',
            self::number => 'Номер',
            self::series => 'Серия',
            self::givenBy => 'Кем выдан',
            self::passportId => 'ID Паспорта',
        };
    }
}
