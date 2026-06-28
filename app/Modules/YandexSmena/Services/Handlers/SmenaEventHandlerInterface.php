<?php

namespace Modules\YandexSmena\Services\Handlers;

interface SmenaEventHandlerInterface
{
    /**
     * Handle an incoming Yandex.Smena event.
     */
    public function handle(array $event): void;
}
