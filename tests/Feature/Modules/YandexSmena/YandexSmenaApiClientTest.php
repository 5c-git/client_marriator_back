<?php

namespace Tests\Feature\Modules\YandexSmena;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\YandexSmena\Exceptions\YandexSmenaApiException;
use Modules\YandexSmena\Services\YandexSmenaApiClient;
use Tests\TestCase;

class YandexSmenaApiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_event_sends_correct_payload(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/publish' => Http::response('', 200),
        ]);

        $client = new YandexSmenaApiClient('https://smena.yandex.ru', 'test-token');
        $client->publishEvent([
            'event_type' => 'provider.shift.create',
            'payload' => ['site_id' => 'site-1'],
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://smena.yandex.ru/api/v1/events/publish'
                && $request->header('Authorization') === ['Bearer test-token']
                && $request['event_type'] === 'provider.shift.create';
        });
    }

    public function test_publish_event_throws_on_failure(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/publish' => Http::response(['error' => 'bad request'], 400),
        ]);

        $client = new YandexSmenaApiClient('https://smena.yandex.ru', 'test-token');

        $this->expectException(YandexSmenaApiException::class);

        $client->publishEvent(['event_type' => 'provider.shift.create', 'payload' => []]);
    }

    public function test_poll_events_returns_batch(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/poll*' => Http::response([
                'events' => [
                    [
                        'event_id' => 'event-1',
                        'event_type' => 'smena.shift.signup_worker',
                        'event_ts' => '2026-01-24T09:00:00Z',
                        'entity_type' => 'shift',
                        'entity_id' => 'shift-1',
                        'payload' => ['worker_id' => 'worker-42'],
                    ],
                ],
                'has_next' => false,
            ], 200),
        ]);

        $client = new YandexSmenaApiClient('https://smena.yandex.ru', 'test-token');
        $result = $client->pollEvents('cursor-1', 10);

        $this->assertCount(1, $result['events']);
        $this->assertFalse($result['has_next']);
    }

    public function test_get_worker_returns_personal_data(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/worker/worker-42' => Http::response([
                'inn' => '123456789012',
                'full_name' => 'Иванов Иван Иванович',
                'phone' => '+79990001122',
                'snils' => '12345678901',
            ], 200),
        ]);

        $client = new YandexSmenaApiClient('https://smena.yandex.ru', 'test-token');
        $worker = $client->getWorker('worker-42');

        $this->assertSame('Иванов Иван Иванович', $worker['full_name']);
    }
}
