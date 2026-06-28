<?php

namespace Modules\YandexSmena\Console\Commands;

use Illuminate\Console\Command;
use Modules\YandexSmena\Services\SmenaEventProcessor;

class PollYandexSmenaEventsCommand extends Command
{
    protected $signature = 'yandex-smena:poll-events';

    protected $description = 'Poll incoming events from Yandex.Smena';

    public function handle(SmenaEventProcessor $processor): int
    {
        $this->info('Polling Yandex.Smena events...');

        $processor->run();

        $this->info('Done.');

        return self::SUCCESS;
    }
}
