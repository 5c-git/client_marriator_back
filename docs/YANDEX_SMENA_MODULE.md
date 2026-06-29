# Yandex.Smena Integration Module

Independent module for integrating Marriator with the Yandex.Smena API.

## Location

`app/Modules/YandexSmena/`

## Purpose

When internal search for an executor fails, the user can publish a shift to Yandex.Smena. The module handles:

- storing reference data mappings (`site_id`, `profession_id`, `payment_id`) provided by Yandex during onboarding;
- publishing shifts from `OrderActivities` or `TaskActivity`;
- polling incoming events from Yandex.Smena;
- processing signup/withdraw/fact/result events;
- approving/rejecting candidates;
- marking shift attendance/fact;
- rating workers and managing favorites/blocks.

## Structure

```text
app/Modules/YandexSmena/
├── Config/
│   └── config.php                         # host, token, queue, reference mappings
├── Console/
│   └── Commands/
│       └── PollYandexSmenaEventsCommand.php
├── Database/
│   └── Migrations/
│       ├── 2025_06_24_000000_create_yandex_smena_sites_table.php
│       ├── 2025_06_24_000001_create_yandex_smena_professions_table.php
│       ├── 2025_06_24_000002_create_yandex_smena_payments_table.php
│       ├── 2025_06_24_000003_create_yandex_smena_shifts_table.php
│       ├── 2025_06_24_000004_create_yandex_smena_candidates_table.php
│       ├── 2025_06_24_000005_create_yandex_smena_favorite_workers_table.php
│       ├── 2025_06_24_000006_update_yandex_smena_mapping_tables.php
│       ├── 2025_06_24_000007_update_yandex_smena_shifts_table.php
│       ├── 2025_06_24_000008_create_yandex_smena_event_log_table.php
│       └── 2025_06_24_000009_create_yandex_smena_poll_state_table.php
├── Jobs/
│   └── PublishYandexSmenaEventJob.php
├── Models/
│   ├── SmenaSite.php
│   ├── SmenaProfession.php
│   ├── SmenaPayment.php
│   ├── SmenaShift.php
│   ├── SmenaCandidate.php
│   ├── SmenaFavoriteWorker.php
│   ├── SmenaEventLog.php
│   └── SmenaPollState.php
├── Providers/
│   └── YandexSmenaServiceProvider.php
├── Routes/
│   └── api.php
└── Services/
    ├── YandexSmenaApiClient.php
    ├── YandexSmenaApiClientInterface.php
    ├── YandexSmenaEventPublisher.php
    ├── EventEnvelopeBuilder.php
    ├── SmenaShiftPublisher.php
    ├── SmenaShiftLifecycleService.php
    ├── SmenaWorkerInteractionService.php
    ├── SmenaEventProcessor.php
    ├── Mappers/
    │   ├── SiteMapper.php
    │   ├── ProfessionMapper.php
    │   ├── PaymentMapper.php
    │   └── ShiftMapper.php
    └── Handlers/
        ├── SmenaEventHandlerInterface.php
        ├── SignupWorkerHandler.php
        ├── WithdrawWorkerHandler.php
        ├── AdjustFactHandler.php
        └── EventResultHandler.php
```

## API Architecture

Yandex.Smena uses an event-driven API:

- **Outgoing events** are sent via `POST /api/v1/events/publish`. The endpoint returns `200 OK` immediately; the real processing result arrives later through polling.
- **Incoming events** are retrieved via `GET /api/v1/events/poll` using a cursor (`last_event_id`).
- **Worker data** is fetched via `GET /api/v1/worker/{worker_id}`.

### Event envelope

```json
{
  "event_id": "uuid",
  "event_type": "provider.shift.create",
  "event_ts": "2026-01-23T08:00:00.000000Z",
  "entity_type": "shift",
  "entity_id": "shift-001",
  "payload": { ... }
}
```

The module guarantees:

- unique `event_id` per outgoing event;
- monotonically increasing `event_ts` per `entity_type` + `entity_id` pair.

### Outgoing event types

| Event type | Entity | Service |
|---|---|---|
| `provider.shift.create` | `shift` | `SmenaShiftPublisher::create()` |
| `provider.shift.cancel` | `shift` | `SmenaShiftPublisher::cancel()` |
| `provider.shift.resume` | `shift` | `SmenaShiftPublisher::resume()` |
| `provider.shift.approve_worker` | `shift` | `SmenaShiftLifecycleService::approveWorker()` |
| `provider.shift.reject_worker` | `shift` | `SmenaShiftLifecycleService::rejectWorker()` |
| `provider.shift.set_code` | `shift` | `SmenaShiftLifecycleService::setCode()` |
| `provider.shift.start` | `shift` | `SmenaShiftLifecycleService::start()` |
| `provider.shift.set_fact` | `shift` | `SmenaShiftLifecycleService::setFact()` |
| `provider.shift.rate_worker` | `shift` | `SmenaShiftLifecycleService::rateWorker()` |
| `provider.worker.block` | `worker` | `SmenaWorkerInteractionService::blockWorker()` |
| `provider.worker.unblock` | `worker` | `SmenaWorkerInteractionService::unblockWorker()` |
| `provider.worker.like` | `worker` | `SmenaWorkerInteractionService::likeWorker()` |
| `provider.worker.unlike` | `worker` | `SmenaWorkerInteractionService::unlikeWorker()` |

Every outgoing event is wrapped in `PublishYandexSmenaEventJob` and pushed to the `yandex-smena` queue.

### Incoming event types

| Event type | Handler | Action |
|---|---|---|
| `smena.shift.signup_worker` | `SignupWorkerHandler` | Creates/updates a candidate, fetches worker data, sets shift status to `assigned` |
| `smena.shift.withdraw_worker` | `WithdrawWorkerHandler` | Marks candidate as `withdrawn`, returns shift to `available` |
| `smena.shift.adjust_fact` | `AdjustFactHandler` | Stores adjusted fact in the shift response field |
| `smena.event.result` | `EventResultHandler` | Correlates with the source event, updates shift status / sync error |

## Provider Registration

`YandexSmenaServiceProvider` (`app/Modules/YandexSmena/Providers/YandexSmenaServiceProvider.php`) is registered in `bootstrap/providers.php`. It loads migrations, config, routes, commands, and binds the API client, publisher, services, and event handlers.

## Configuration

`app/Modules/YandexSmena/Config/config.php` (published to `config/yandex-smena.php`):

```php
return [
    'host' => env('YANDEX_SMENA_HOST', 'https://smena.yandex.ru'),
    'token' => env('YANDEX_SMENA_TOKEN', ''),
    'queue' => env('YANDEX_SMENA_QUEUE', 'yandex-smena'),

    // Reference data provided by Yandex during onboarding.
    'sites' => [
        // 'place_uuid_or_id' => 'yandex_site_id',
    ],
    'professions' => [
        // 'view_activity_uuid_or_id' => 'yandex_profession_id',
    ],
    'payments' => [
        // 'local_code' => [
        //     'name' => '...',
        //     'payment_id' => 'yandex_payment_id',
        //     'amount_per_hour' => 100,
        //     'currency' => 'RUB',
        // ],
    ],
];
```

## Reference Data

`site_id`, `profession_id`, and `payment_id` are created by Yandex.Smena and handed over during onboarding. There is no API to create them. Store them in the mapping tables (`yandex_smena_sites`, `yandex_smena_professions`, `yandex_smena_payments`) via admin or a one-time seeder.

## Polling

Incoming events are polled, not pushed via webhooks. Run the command manually or schedule it every minute:

```bash
./vendor/bin/sail exec laravel.test php artisan yandex-smena:poll-events
```

The command is already scheduled in `routes/console.php`:

```php
Schedule::command('yandex-smena:poll-events')->everyMinute()->withoutOverlapping();
```

## Publishing a Shift

```php
use Modules\YandexSmena\Services\SmenaShiftPublisher;

$publisher->create($smenaShift);
```

`SmenaShift` must be linked to mapping records that have a Yandex `external_id`. The publisher builds the payload and dispatches `provider.shift.create`.

## Candidate Lifecycle

When `smena.shift.signup_worker` arrives:

1. `SignupWorkerHandler` creates a `SmenaCandidate` with status `pending`.
2. The supervisor/manager calls `SmenaShiftLifecycleService::approveWorker()` or `rejectWorker()`.
3. Yandex.Smena processes the event and returns `smena.event.result`; `EventResultHandler` updates the local state.

## Mapping to Existing Entities

| Yandex.Smena | Marriator |
|---|---|
| Site | `Place` (`directory_place`) |
| Profession | `ViewActivities` (`directory_view_activities`) |
| Payment | `SmenaProfession.yandex_smena_payment_id` → `SmenaPayment` |
| Shift | `OrderActivities` or `TaskActivity` |
| Candidate | external worker |

## Integration Endpoint

`POST /api/yandex-smena/publish-shift`

Middleware: `auth:api`, `scope:personalArea`, role `manager` or `supervisor`.

Payload (one of the pairs):

```json
{
  "orderId": 1,
  "orderActivityId": 2
}
```

or

```json
{
  "taskId": 1,
  "taskActivityId": 2
}
```

### What happens

1. `PublishYandexSmenaShiftRequest` validates ownership and activity linkage.
2. `PublishYandexSmenaShiftController` loads the activity.
3. `SmenaShiftFactory::fromActivity()` resolves:
   - `SmenaSite` from `Order.place_id` / `Task.place_id`;
   - `SmenaProfession` from `activity.view_activity_id`;
   - `SmenaPayment` from `SmenaProfession.yandex_smena_payment_id`.
4. It computes `start_at`, `length_min`, `rest_length_min` (from profession mapping).
5. It creates `activity.count` separate `SmenaShift` records, each with a unique `entity_id`.
6. Each shift is published via `SmenaShiftPublisher::create()` → `provider.shift.create`.

### Required mapping setup

- `yandex_smena_sites`: `place_id` → Yandex `site_id`.
- `yandex_smena_professions`: `view_activity_id` → Yandex `profession_id`; set `rest_length_min`; set `yandex_smena_payment_id`.
- `yandex_smena_payments`: Yandex `payment_id`.

## Queue

The module uses the `yandex-smena` Redis queue. A Horizon supervisor is added in `config/horizon.php`.

## Tests

Tests live in `tests/Feature/Modules/YandexSmena/`.

Run module tests:

```bash
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit tests/Feature/Modules/YandexSmena
```

## Next Steps

- Build admin UI for mapping management and event log inspection.
- Add retry/monitoring for permanently failed outgoing events.
