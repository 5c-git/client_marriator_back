<?php

namespace Modules\YandexSmena\Services;

interface YandexSmenaApiClientInterface
{
    /**
     * Publish an event to Yandex.Smena.
     *
     * @throws YandexSmenaApiException
     */
    public function publishEvent(array $envelope): void;

    /**
     * Poll events from Yandex.Smena.
     *
     * @return array{events: array<int, array>, has_next: bool}
     *
     * @throws YandexSmenaApiException
     */
    public function pollEvents(?string $lastEventId = null, int $limit = 100): array;

    /**
     * Fetch worker personal data.
     *
     * @return array{inn: string, full_name: string, phone: string, snils?: string|null}
     *
     * @throws YandexSmenaApiException
     */
    public function getWorker(string $workerId): array;
}
