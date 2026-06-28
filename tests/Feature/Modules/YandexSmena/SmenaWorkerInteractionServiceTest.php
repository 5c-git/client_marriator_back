<?php

namespace Tests\Feature\Modules\YandexSmena;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\YandexSmena\Jobs\PublishYandexSmenaEventJob;
use Modules\YandexSmena\Services\SmenaWorkerInteractionService;
use Tests\TestCase;

class SmenaWorkerInteractionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_worker_dispatches_event(): void
    {
        Queue::fake();

        app(SmenaWorkerInteractionService::class)->blockWorker(
            'worker-42',
            'incident',
            'temporary',
            Carbon::parse('2026-02-01 00:00:00', 'UTC'),
            'site-1',
            null,
            'Нарушение'
        );

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.worker.block'
                && $envelope['entity_type'] === 'worker'
                && $envelope['entity_id'] === 'worker-42'
                && $envelope['payload']['reason'] === 'incident'
                && $envelope['payload']['block_type'] === 'temporary';
        });
    }

    public function test_block_worker_requires_blocked_until_for_temporary(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(SmenaWorkerInteractionService::class)->blockWorker('worker-42', 'incident', 'temporary');
    }

    public function test_unblock_worker_dispatches_event(): void
    {
        Queue::fake();

        app(SmenaWorkerInteractionService::class)->unblockWorker('worker-42', 'site-1');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.worker.unblock'
                && $envelope['payload']['site_id'] === 'site-1';
        });
    }

    public function test_like_worker_dispatches_event(): void
    {
        Queue::fake();

        app(SmenaWorkerInteractionService::class)->likeWorker('worker-42', 'site-1', 'Хороший работник');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.worker.like'
                && $envelope['payload']['site_id'] === 'site-1';
        });
    }

    public function test_unlike_worker_dispatches_event(): void
    {
        Queue::fake();

        app(SmenaWorkerInteractionService::class)->unlikeWorker('worker-42', 'site-1');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.worker.unlike'
                && $envelope['payload']['site_id'] === 'site-1';
        });
    }
}
