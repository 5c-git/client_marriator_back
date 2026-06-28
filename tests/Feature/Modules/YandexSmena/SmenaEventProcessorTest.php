<?php

namespace Tests\Feature\Modules\YandexSmena;

use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaEventLog;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Models\SmenaProfession;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Models\SmenaSite;
use Modules\YandexSmena\Services\SmenaEventProcessor;
use Tests\TestCase;

class SmenaEventProcessorTest extends TestCase
{
    use RefreshDatabase;

    private function createShift(): SmenaShift
    {
        $brand = Brand::create(['uuid' => 'brand-1', 'name' => 'Brand 1']);
        $place = Place::create([
            'uuid' => 'place-1',
            'brand_id' => $brand->id,
            'name' => 'ТЦ',
            'address_kladr' => 'Москва',
            'latitude' => 55.0,
            'longitude' => 37.0,
        ]);

        $site = SmenaSite::create([
            'place_id' => $place->id,
            'external_id' => 'yandex-site-1',
            'name' => 'ТЦ',
        ]);

        $profession = SmenaProfession::create([
            'view_activity_id' => ViewActivities::create(['uuid' => 'activity-1', 'name' => 'Продавец', 'active' => true])->id,
            'external_id' => 'yandex-prof-1',
            'name' => 'Продавец',
        ]);

        $payment = SmenaPayment::create([
            'code' => 'PAY',
            'external_id' => 'yandex-pay-1',
            'name' => 'Тариф',
            'amount_per_hour' => 100,
            'currency' => 'RUB',
        ]);

        return SmenaShift::create([
            'entity_id' => 'shift-001',
            'yandex_smena_site_id' => $site->id,
            'yandex_smena_profession_id' => $profession->id,
            'yandex_smena_payment_id' => $payment->id,
            'start_at' => Carbon::parse('2026-07-05 10:00:00', 'Europe/Moscow'),
            'length_min' => 480,
            'rest_length_min' => 60,
            'payload' => [],
        ]);
    }

    public function test_signup_worker_creates_candidate(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/poll*' => Http::response([
                'events' => [
                    [
                        'event_id' => 'event-1',
                        'event_type' => 'smena.shift.signup_worker',
                        'event_ts' => '2026-01-24T09:00:00Z',
                        'entity_type' => 'shift',
                        'entity_id' => 'shift-001',
                        'payload' => [
                            'worker_id' => 'worker-42',
                            'is_favorite' => false,
                        ],
                    ],
                ],
                'has_next' => false,
            ], 200),
            'https://smena.yandex.ru/api/v1/worker/worker-42' => Http::response([
                'inn' => '123456789012',
                'full_name' => 'Иванов Иван Иванович',
                'phone' => '+79990001122',
            ], 200),
        ]);

        $this->createShift();

        app(SmenaEventProcessor::class)->run();

        $candidate = SmenaCandidate::query()
            ->where('external_worker_id', 'worker-42')
            ->first();

        $this->assertNotNull($candidate);
        $this->assertSame('pending', $candidate->status);
        $this->assertSame('Иванов', $candidate->last_name);
        $this->assertSame('Иван', $candidate->first_name);
    }

    public function test_withdraw_worker_updates_candidate_status(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/poll*' => Http::response([
                'events' => [
                    [
                        'event_id' => 'event-2',
                        'event_type' => 'smena.shift.withdraw_worker',
                        'event_ts' => '2026-01-24T09:05:00Z',
                        'entity_type' => 'shift',
                        'entity_id' => 'shift-001',
                        'payload' => [
                            'worker_id' => 'worker-42',
                            'is_favorite' => false,
                            'reason' => 'cancelled_by_worker',
                        ],
                    ],
                ],
                'has_next' => false,
            ], 200),
        ]);

        $shift = $this->createShift();
        SmenaCandidate::create([
            'yandex_smena_shift_id' => $shift->id,
            'external_worker_id' => 'worker-42',
            'status' => 'pending',
        ]);

        app(SmenaEventProcessor::class)->run();

        $candidate = SmenaCandidate::query()
            ->where('external_worker_id', 'worker-42')
            ->first();

        $this->assertSame('withdrawn', $candidate->status);
    }

    public function test_event_result_updates_source_log_and_shift_status(): void
    {
        $shift = $this->createShift();

        SmenaEventLog::query()->create([
            'event_id' => 'provider-event-1',
            'event_type' => 'provider.shift.create',
            'event_ts' => '2026-01-24T09:00:00.000000Z',
            'direction' => 'out',
            'entity_type' => 'shift',
            'entity_id' => 'shift-001',
            'payload' => [
                'event_id' => 'provider-event-1',
                'event_type' => 'provider.shift.create',
                'payload' => [],
            ],
        ]);

        Http::fake([
            'https://smena.yandex.ru/api/v1/events/poll*' => Http::response([
                'events' => [
                    [
                        'event_id' => 'event-3',
                        'event_type' => 'smena.event.result',
                        'event_ts' => '2026-01-24T09:01:00Z',
                        'entity_type' => 'shift',
                        'entity_id' => 'shift-001',
                        'payload' => [
                            'source_event_id' => 'provider-event-1',
                            'source_event_type' => 'provider.shift.create',
                            'status' => 'success',
                        ],
                    ],
                ],
                'has_next' => false,
            ], 200),
        ]);

        app(SmenaEventProcessor::class)->run();

        $shift->refresh();
        $this->assertSame('available', $shift->external_status);

        $log = SmenaEventLog::query()->where('event_id', 'provider-event-1')->first();
        $this->assertSame('success', $log->response['status']);
    }

    public function test_duplicate_incoming_event_is_skipped(): void
    {
        Http::fake([
            'https://smena.yandex.ru/api/v1/events/poll*' => Http::response([
                'events' => [
                    [
                        'event_id' => 'event-4',
                        'event_type' => 'smena.shift.signup_worker',
                        'event_ts' => '2026-01-24T09:00:00Z',
                        'entity_type' => 'shift',
                        'entity_id' => 'shift-001',
                        'payload' => [
                            'worker_id' => 'worker-42',
                            'is_favorite' => false,
                        ],
                    ],
                ],
                'has_next' => false,
            ], 200),
            'https://smena.yandex.ru/api/v1/worker/worker-42' => Http::response([
                'inn' => '123456789012',
                'full_name' => 'Иванов Иван',
                'phone' => '+79990001122',
            ], 200),
        ]);

        $this->createShift();

        $processor = app(SmenaEventProcessor::class);
        $processor->run();
        $processor->run();

        $this->assertCount(1, SmenaCandidate::all());
    }
}
