<?php

namespace Modules\YandexSmena\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\YandexSmena\Exceptions\YandexSmenaApiException;
use Modules\YandexSmena\Models\SmenaEventLog;
use Modules\YandexSmena\Services\YandexSmenaApiClientInterface;

class PublishYandexSmenaEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(private readonly array $envelope)
    {
    }

    public function envelope(): array
    {
        return $this->envelope;
    }

    public function handle(YandexSmenaApiClientInterface $client): void
    {
        $log = $this->ensureLogEntry();

        try {
            $client->publishEvent($this->envelope);

            $log->update([
                'response' => ['status' => 'accepted'],
                'error' => null,
                'processed_at' => now(),
            ]);
        } catch (YandexSmenaApiException $e) {
            Log::channel('single')->error('Yandex.Smena event publish failed', [
                'envelope' => $this->envelope,
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            $log->update([
                'response' => $e->getResponseBody(),
                'error' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            if ($e->getRetryAfter() !== null && $e->getRetryAfter() > 0) {
                $this->release($e->getRetryAfter());

                return;
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('single')->error('Yandex.Smena event publish job failed permanently', [
            'envelope' => $this->envelope,
            'error' => $exception->getMessage(),
        ]);
    }

    private function ensureLogEntry(): SmenaEventLog
    {
        return SmenaEventLog::query()->updateOrCreate(
            ['event_id' => $this->envelope['event_id']],
            [
                'event_type' => $this->envelope['event_type'],
                'event_ts' => $this->envelope['event_ts'],
                'direction' => 'out',
                'entity_type' => $this->envelope['entity_type'] ?? null,
                'entity_id' => $this->envelope['entity_id'] ?? null,
                'payload' => $this->envelope,
            ]
        );
    }
}
