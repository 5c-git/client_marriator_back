<?php

namespace Tests\Feature\Modules\YandexSmena;

use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\YandexSmena\Jobs\PublishYandexSmenaEventJob;
use Modules\YandexSmena\Models\SmenaCandidate;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Models\SmenaProfession;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Models\SmenaSite;
use Modules\YandexSmena\Services\SmenaShiftLifecycleService;
use Tests\TestCase;

class SmenaShiftLifecycleServiceTest extends TestCase
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

    public function test_approve_worker_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();
        SmenaCandidate::create([
            'yandex_smena_shift_id' => $shift->id,
            'external_worker_id' => 'worker-42',
            'status' => 'pending',
        ]);

        app(SmenaShiftLifecycleService::class)->approveWorker($shift, 'worker-42');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.approve_worker'
                && $envelope['payload']['worker_id'] === 'worker-42';
        });
    }

    public function test_reject_worker_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->rejectWorker($shift, 'worker-42', 'need_other_worker');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.reject_worker'
                && $envelope['payload']['worker_id'] === 'worker-42'
                && $envelope['payload']['reason'] === 'need_other_worker';
        });
    }

    public function test_set_code_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->setCode($shift, 'worker-42', 'ABC123', Carbon::parse('2026-07-05 12:00:00', 'Europe/Moscow'));

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.set_code'
                && $envelope['payload']['worker_id'] === 'worker-42'
                && $envelope['payload']['code'] === 'ABC123';
        });
    }

    public function test_start_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->start($shift, 'worker-42', Carbon::parse('2026-07-05 10:05:00', 'Europe/Moscow'));

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.start'
                && $envelope['payload']['worker_id'] === 'worker-42';
        });
    }

    public function test_set_fact_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->setFact($shift, 'worker-42', false, 450, 60);

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.set_fact'
                && $envelope['payload']['worker_id'] === 'worker-42'
                && $envelope['payload']['is_absent'] === false
                && $envelope['payload']['fact_length'] === 450;
        });
    }

    public function test_rate_worker_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->rateWorker($shift, 'worker-42', 5, 'Отлично');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.rate_worker'
                && $envelope['payload']['worker_id'] === 'worker-42'
                && $envelope['payload']['rating'] === 5;
        });
    }

    public function test_set_fact_without_fact_length_dispatches_event(): void
    {
        Queue::fake();
        $shift = $this->createShift();

        app(SmenaShiftLifecycleService::class)->setFact($shift, 'worker-42', false);

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.set_fact'
                && $envelope['payload']['worker_id'] === 'worker-42'
                && $envelope['payload']['is_absent'] === false
                && ! array_key_exists('fact_length', $envelope['payload']);
        });
    }

    public function test_rate_worker_validates_rating(): void
    {
        $shift = $this->createShift();

        $this->expectException(\InvalidArgumentException::class);
        app(SmenaShiftLifecycleService::class)->rateWorker($shift, 'worker-42', 6);
    }
}
