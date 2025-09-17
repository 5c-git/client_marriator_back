<?php

namespace App\Enum\Order;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum ReportStatusEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case start = 1;
    case end = 2;
    case reported = 3;
    case notEnded = 4;

    public function getStatusName(): string
    {
        return match($this)
        {
            self::start => 'День запущен',
            self::end => 'День завершён',
            self::reported => 'Репорт отправлен',
            self::notEnded => 'Не окончен',
        };
    }
}
