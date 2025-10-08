<?php

namespace App\Enum\Document;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentFieldTypeEnum: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case Passport = 'c5gAyG7YPWV7RiCx23srwQnYV8bv5U';

    public function geFieldType(): DocumentTypeEnum
    {
        return match($this)
        {
            self::Passport => DocumentTypeEnum::Passport,
        };
    }
}
