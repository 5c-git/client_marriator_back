<?php

namespace App\Enum\Document;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum RecognitionDocumentStatusEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case pending = 1;
    case processing = 2;
    case recognized = 3;
    case failed = 4;
}
