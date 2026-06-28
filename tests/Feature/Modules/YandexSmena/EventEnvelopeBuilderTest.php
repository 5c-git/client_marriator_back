<?php

namespace Tests\Feature\Modules\YandexSmena;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\YandexSmena\Models\SmenaEventLog;
use Modules\YandexSmena\Services\EventEnvelopeBuilder;
use Tests\TestCase;

class EventEnvelopeBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_envelope_has_required_fields(): void
    {
        $builder = new EventEnvelopeBuilder();
        $envelope = $builder->build('provider.shift.create', 'shift', 'shift-1', ['site_id' => 'site-1']);

        $this->assertArrayHasKey('event_id', $envelope);
        $this->assertSame('provider.shift.create', $envelope['event_type']);
        $this->assertSame('shift', $envelope['entity_type']);
        $this->assertSame('shift-1', $envelope['entity_id']);
        $this->assertSame(['site_id' => 'site-1'], $envelope['payload']);
    }

    public function test_event_ts_is_monotonic_per_entity(): void
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        SmenaEventLog::query()->create([
            'event_id' => 'event-1',
            'event_type' => 'provider.shift.create',
            'direction' => 'out',
            'entity_type' => 'shift',
            'entity_id' => 'shift-1',
            'payload' => [],
            'event_ts' => '2026-01-01T12:00:00.000000Z',
        ]);

        Carbon::setTestNow('2026-01-01 11:59:59');

        $builder = new EventEnvelopeBuilder();
        $envelope = $builder->build('provider.shift.cancel', 'shift', 'shift-1', []);

        $this->assertTrue(
            \Carbon\Carbon::parse($envelope['event_ts'])->greaterThan(
                \Carbon\Carbon::parse('2026-01-01T12:00:00.000000Z')
            )
        );
    }
}
