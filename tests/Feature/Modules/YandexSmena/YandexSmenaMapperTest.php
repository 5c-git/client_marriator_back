<?php

namespace Tests\Feature\Modules\YandexSmena;

use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\ViewActivities;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\YandexSmena\Models\SmenaPayment;
use Modules\YandexSmena\Services\Mappers\PaymentMapper;
use Modules\YandexSmena\Services\Mappers\ProfessionMapper;
use Modules\YandexSmena\Services\Mappers\ShiftMapper;
use Modules\YandexSmena\Services\Mappers\SiteMapper;
use Tests\TestCase;

class YandexSmenaMapperTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_mapper_builds_payload(): void
    {
        $place = new Place([
            'name' => 'ТЦ Европейский',
            'address_kladr' => 'г. Москва, пл. Киевского вокзала, 2',
            'latitude' => 55.7448,
            'longitude' => 37.5654,
        ]);

        $mapper = new SiteMapper();
        $payload = $mapper->toPayload($place, 'external-site-1');

        $this->assertSame('ТЦ Европейский', $payload['name']);
        $this->assertSame('external-site-1', $payload['id']);
        $this->assertSame(55.7448, $payload['latitude']);
    }

    public function test_profession_mapper_builds_payload(): void
    {
        $activity = new ViewActivities([
            'name' => 'Продавец-кассир',
        ]);

        $mapper = new ProfessionMapper();
        $payload = $mapper->toPayload($activity);

        $this->assertSame('Продавец-кассир', $payload['name']);
        $this->assertArrayNotHasKey('id', $payload);
    }

    public function test_payment_mapper_builds_payload(): void
    {
        $payment = new SmenaPayment([
            'code' => 'PAY_200',
            'name' => 'Тариф 200',
            'external_id' => 'pay-200',
        ]);

        $mapper = new PaymentMapper();
        $payload = $mapper->toPayload($payment);

        $this->assertSame('Тариф 200', $payload['name']);
        $this->assertSame('pay-200', $payload['id']);
    }

    public function test_shift_mapper_formats_utc_datetime(): void
    {
        $mapper = new ShiftMapper();
        $payload = $mapper->toPayload([
            'site_id' => 'site-1',
            'profession_id' => 'prof-1',
            'payment_id' => 'pay-1',
            'start_at' => Carbon::parse('2026-07-05 10:00:00', 'Europe/Moscow'),
            'length_min' => 480,
            'rest_length_min' => 60,
        ]);

        $this->assertSame('site-1', $payload['site_id']);
        $this->assertSame(480, $payload['length']);
        $this->assertSame('2026-07-05T07:00:00Z', $payload['start_at']);
    }
}
