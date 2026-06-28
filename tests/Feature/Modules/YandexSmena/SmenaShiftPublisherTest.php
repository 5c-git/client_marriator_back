<?php

namespace Tests\Feature\Modules\YandexSmena;

use App\Models\Fields\Directory\Brand;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\YandexSmena\Jobs\PublishYandexSmenaEventJob;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Models\SmenaProfession;
use Modules\YandexSmena\Models\SmenaShift;
use Modules\YandexSmena\Models\SmenaSite;
use Modules\YandexSmena\Services\SmenaShiftPublisher;
use Tests\TestCase;

class SmenaShiftPublisherTest extends TestCase
{
    use RefreshDatabase;

    private function createShift(): SmenaShift
    {
        $brand = Brand::create(['uuid' => 'brand-1', 'name' => 'Brand 1']);
        $place = Place::create([
            'uuid' => 'place-1',
            'brand_id' => $brand->id,
            'name' => 'ТЦ Европейский',
            'address_kladr' => 'г. Москва',
            'latitude' => 55.7448,
            'longitude' => 37.5654,
        ]);

        $site = SmenaSite::create([
            'place_id' => $place->id,
            'external_id' => 'yandex-site-1',
            'name' => 'ТЦ Европейский',
        ]);

        $activity = ViewActivities::create(['uuid' => 'activity-1', 'name' => 'Продавец', 'active' => true]);
        $profession = SmenaProfession::create([
            'view_activity_id' => $activity->id,
            'external_id' => 'yandex-prof-1',
            'name' => 'Продавец',
        ]);

        $payment = SmenaPayment::create([
            'code' => 'PAY_200',
            'external_id' => 'yandex-pay-1',
            'name' => 'Тариф 200',
            'amount_per_hour' => 200,
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

    public function test_create_dispatches_provider_shift_create_event(): void
    {
        Queue::fake();

        $shift = $this->createShift();
        $publisher = app(SmenaShiftPublisher::class);
        $publisher->create($shift);

        $shift->refresh();
        $this->assertNotNull($shift->published_at);

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.create'
                && $envelope['entity_type'] === 'shift'
                && $envelope['entity_id'] === 'shift-001'
                && $envelope['payload']['site_id'] === 'yandex-site-1'
                && $envelope['payload']['profession_id'] === 'yandex-prof-1'
                && $envelope['payload']['payment_id'] === 'yandex-pay-1'
                && $envelope['payload']['length'] === 480;
        });
    }

    public function test_create_with_favorites(): void
    {
        Queue::fake();

        $shift = $this->createShift();
        $publisher = app(SmenaShiftPublisher::class);
        $publisher->create($shift, ['worker-1', 'worker-2'], true);

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.create'
                && $envelope['payload']['favorites_worker_ids'] === ['worker-1', 'worker-2']
                && $envelope['payload']['favorites_only'] === true;
        });
    }

    public function test_create_throws_when_mapping_external_id_missing(): void
    {
        Queue::fake();

        $shift = $this->createShift();
        $shift->site->update(['external_id' => null]);

        $publisher = app(SmenaShiftPublisher::class);

        $this->expectException(\Modules\YandexSmena\Exceptions\YandexSmenaConfigurationException::class);
        $publisher->create($shift);
    }

    public function test_cancel_dispatches_provider_shift_cancel_event(): void
    {
        Queue::fake();

        $shift = $this->createShift();
        $publisher = app(SmenaShiftPublisher::class);
        $publisher->cancel($shift, 'service_not_needed', 'Смена больше не нужна');

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.cancel'
                && $envelope['payload']['reason'] === 'service_not_needed'
                && $envelope['payload']['comment'] === 'Смена больше не нужна';
        });
    }

    public function test_resume_dispatches_provider_shift_resume_event(): void
    {
        Queue::fake();

        $shift = $this->createShift();
        $publisher = app(SmenaShiftPublisher::class);
        $publisher->resume($shift);

        Queue::assertPushed(PublishYandexSmenaEventJob::class, function ($job) {
            $envelope = $job->envelope();

            return $envelope['event_type'] === 'provider.shift.resume'
                && $envelope['entity_type'] === 'shift'
                && $envelope['entity_id'] === 'shift-001';
        });
    }
}
