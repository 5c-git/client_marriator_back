<?php

namespace App\Enum\Document;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentStatusEnum: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case Signed = 'signed';

    case Archive = 'archive';
    case Inquiries = 'inquiries';

}
