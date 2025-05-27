<?php

namespace App\Enum\Order;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum OrderStatusEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case new = 1;
    case notAccepted = 2;
    case accepted = 3;
    case canceled = 4;
    case archive = 5;

    public function getStatusName(): string
    {
        return match($this)
        {
            self::new => 'Новый',
            self::notAccepted => 'Не принято',
            self::accepted => 'Принято',
            self::canceled => 'Отменено',
            self::archive => 'Архив',
        };
    }
}
