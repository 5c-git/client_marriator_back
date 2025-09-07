<?php

namespace App\Enum\Order;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum BidAcceptingStatusEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case notAccepted = 1;
    case accepted = 2;
    case declined = 3;

    public function getStatusName(): string
    {
        return match($this)
        {
            self::notAccepted => 'Не принято',
            self::accepted => 'Принято',
            self::declined => 'Отклонено',
        };
    }
}
