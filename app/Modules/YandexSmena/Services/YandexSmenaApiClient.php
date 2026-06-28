<?php

namespace Modules\YandexSmena\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Modules\YandexSmena\Exceptions\YandexSmenaApiException;

class YandexSmenaApiClient implements YandexSmenaApiClientInterface
{
    private const PUBLISH_LIMIT = 'yandex-smena-publish';

    private const POLL_LIMIT = 'yandex-smena-poll';

    private const WORKER_LIMIT = 'yandex-smena-worker';

    public function __construct(
        private readonly string $host,
        private readonly string $token,
    ) {
    }

    public function publishEvent(array $envelope): void
    {
        $this->throttlePublish();

        $url = rtrim($this->host, '/').'/api/v1/events/publish';

        Log::channel('single')->debug('Yandex.Smena publish request', [
            'url' => $url,
            'envelope' => $envelope,
        ]);

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post($url, $envelope);

            $response->throw();
        } catch (ConnectionException $e) {
            throw new YandexSmenaApiException(
                'Yandex.Smena API connection error: '.$e->getMessage(),
                0,
                $e
            );
        } catch (RequestException $e) {
            $response = $e->response;
            $status = $response?->status() ?? 0;
            $body = $response?->body() ?? '';

            throw new YandexSmenaApiException(
                "Yandex.Smena API error [{$status}]: ".$body,
                $status,
                $e,
                $response?->json() ?? [],
                $this->resolveRetryAfter($response?->header('Retry-After'))
            );
        }
    }

    public function pollEvents(?string $lastEventId = null, int $limit = 100, array $eventTypes = []): array
    {
        $this->throttlePoll();

        $url = rtrim($this->host, '/').'/api/v1/events/poll';

        $query = array_filter([
            'last_event_id' => $lastEventId,
            'limit' => $limit,
        ], fn ($value) => $value !== null);

        if ($eventTypes !== []) {
            $query['event_type'] = $eventTypes;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($url, $query);

            $response->throw();

            $data = $response->json();

            Log::channel('single')->debug('Yandex.Smena poll response', [
                'events_count' => count($data['events'] ?? []),
                'has_next' => $data['has_next'] ?? false,
            ]);

            return [
                'events' => $data['events'] ?? [],
                'has_next' => $data['has_next'] ?? false,
            ];
        } catch (ConnectionException $e) {
            throw new YandexSmenaApiException(
                'Yandex.Smena poll connection error: '.$e->getMessage(),
                0,
                $e
            );
        } catch (RequestException $e) {
            $response = $e->response;
            $status = $response?->status() ?? 0;
            $body = $response?->body() ?? '';

            throw new YandexSmenaApiException(
                "Yandex.Smena poll error [{$status}]: ".$body,
                $status,
                $e,
                $response?->json() ?? [],
                $this->resolveRetryAfter($response?->header('Retry-After'))
            );
        }
    }

    public function getWorker(string $workerId): array
    {
        $this->throttleWorker();

        $url = rtrim($this->host, '/').'/api/v1/worker/'.urlencode($workerId);

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($url);

            $response->throw();

            return $response->json();
        } catch (ConnectionException $e) {
            throw new YandexSmenaApiException(
                'Yandex.Smena worker connection error: '.$e->getMessage(),
                0,
                $e
            );
        } catch (RequestException $e) {
            $response = $e->response;
            $status = $response?->status() ?? 0;

            throw new YandexSmenaApiException(
                "Yandex.Smena worker error [{$status}]: ".($response?->body() ?? ''),
                $status,
                $e,
                $response?->json() ?? [],
                $this->resolveRetryAfter($response?->header('Retry-After'))
            );
        }
    }

    private function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->token !== '') {
            $headers['Authorization'] = 'Bearer '.$this->token;
        }

        return $headers;
    }

    private function throttlePublish(): void
    {
        // 40 requests per second.
        if (! RateLimiter::tooManyAttempts(self::PUBLISH_LIMIT, 40)) {
            RateLimiter::hit(self::PUBLISH_LIMIT, 1);

            return;
        }

        $seconds = RateLimiter::availableIn(self::PUBLISH_LIMIT);
        Log::channel('single')->warning('Yandex.Smena publish rate limit hit', ['sleep' => $seconds]);
        sleep($seconds);
    }

    private function throttlePoll(): void
    {
        // 60 requests per minute.
        if (! RateLimiter::tooManyAttempts(self::POLL_LIMIT, 60)) {
            RateLimiter::hit(self::POLL_LIMIT, 60);

            return;
        }

        $seconds = RateLimiter::availableIn(self::POLL_LIMIT);
        Log::channel('single')->warning('Yandex.Smena poll rate limit hit', ['sleep' => $seconds]);
        sleep($seconds);
    }

    private function throttleWorker(): void
    {
        // 100 requests per minute.
        if (! RateLimiter::tooManyAttempts(self::WORKER_LIMIT, 100)) {
            RateLimiter::hit(self::WORKER_LIMIT, 60);

            return;
        }

        $seconds = RateLimiter::availableIn(self::WORKER_LIMIT);
        Log::channel('single')->warning('Yandex.Smena worker rate limit hit', ['sleep' => $seconds]);
        sleep($seconds);
    }

    private function resolveRetryAfter(?string $header): ?int
    {
        if ($header === null || $header === '') {
            return null;
        }

        if (is_numeric($header)) {
            return (int) $header;
        }

        // Retry-After may also be an HTTP-date; we do not parse dates here.
        return null;
    }
}
