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
    case offer = 3;
    case offerSelf = 4;

    public function getName(): string
    {
        return match($this)
        {
            self::payment => 'Платежный документ',
            self::details => 'Договор',
            self::offer => 'Договор оферты',
            self::offerSelf => 'Договор оферты для самозанятых',
        };
    }

    public function getTemplate(): string
    {
        return match($this)
        {
            self::payment => 'payment',
            self::details => 'details',
            self::offer => 'offer',
            self::offerSelf => 'offerSelf',
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
            self::offer => [
                'Имя' => '{{$name}}',
                'Фамилия' => '{{$lastName}}',
                'Отчество' => '{{$secondName}}',
                'Итоговая сумма' => '{{$totalAmount}}',
            ],
            self::offerSelf => [
                'Имя' => '{{$name}}',
                'Фамилия' => '{{$lastName}}',
                'Отчество' => '{{$secondName}}',
                'Итоговая сумма' => '{{$totalAmount}}',
            ],
        };
    }
}
