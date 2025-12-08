<?php

namespace App\Enum\Document\DocumentTemplates;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentTemplatesEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case payment = 1;
    case details = 2;

    public function getName(): string
    {
        return match($this)
        {
            self::payment => 'Платежный документ',
            self::details => 'Реквизиты',
        };
    }

    public function getTemplate(): string
    {
        return match($this)
        {
            self::payment => 'payment',
            self::details => 'details',
        };
    }

    public function getFields(): array
    {
        return match($this)
        {
            self::payment => [
                'Имя' => '{{$name}}',
                'Фамилия' => '{{$lastName}}',
                'Отчество' => '{{$secondName}}',
                'Итоговая сумма' => '{{$totalAmount}}',
            ],
            self::details => [
                'Имя' => '{{$name}}',
                'Фамилия' => '{{$lastName}}',
                'Отчество' => '{{$secondName}}',
                'Итоговая сумма' => '{{$totalAmount}}',
            ],
        };
    }
}
