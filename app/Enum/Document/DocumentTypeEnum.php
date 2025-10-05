<?php

namespace App\Enum\Document;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;
use App\Enum\Document\DocumentFieldRecognition\Passport;

enum DocumentTypeEnum: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case Passport = '1';

    static function getEnumByExternalName(string $fileName): ?DocumentTypeEnum
    {
        return match ($fileName) {
            'Паспорт' => self::Passport,
            default   => null,
        };
    }

    public function getRecognitionEnum(): ?string
    {
        return match ($this) {
            self::Passport => Passport::class,
            default   => null,
        };
    }

}
