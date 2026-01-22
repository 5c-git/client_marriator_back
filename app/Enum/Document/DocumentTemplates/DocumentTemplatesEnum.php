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
    case detailsFiz = 2;
    case offer = 3;
    case offerSelf = 4;
    case detailsSelf = 5;

    public function getName(): string
    {
        return match($this)
        {
            self::payment => 'Платежный документ',
            self::detailsFiz => 'Договор для физ лица',
            self::offer => 'Договор оферты',
            self::offerSelf => 'Договор оферты для самозанятых',
            self::detailsSelf => 'Договор для самозанятых',
        };
    }

    public function getTemplate(): string
    {
        return match($this)
        {
            self::payment => 'payment',
            self::detailsFiz => 'details',
            self::offer => 'offer',
            self::offerSelf => 'offerSelf',
            self::detailsSelf => 'detailsSelf',
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
            self::detailsFiz => [
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
            self::detailsSelf => [
                'Имя' => '{{$name}}',
                'Фамилия' => '{{$lastName}}',
                'Отчество' => '{{$secondName}}',
                'Итоговая сумма' => '{{$totalAmount}}',
            ],
        };
    }
}
