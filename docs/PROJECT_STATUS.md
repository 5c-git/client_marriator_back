# Project Status

Snapshot of the Marriator project state and recent implementation work.

## Current Modules

### 1. Questionnaire Module (`app/Modules/Questionnaire/`)

**Status:** Phase 1 complete, tested, integrated.

**What it does:**
- Takes a `User` after `finishRegister && confirmRegister` and builds a structured questionnaire.
- Runs a configurable chain of validation/enrichment steps via Redis queue.
- Supports required and optional steps.
- Stores descriptive user data (`expansion_data`, `error_data`, `requisites_data`) in separate DB columns.
- Maps UUID form field keys to semantic keys (`first_name`, `last_name`, `phone`, `inn`, etc.).

**Key files:**
- `docs/QUESTIONNAIRE_MODULE.md`
- `app/Modules/Questionnaire/Config/config.php`
- `app/Modules/Questionnaire/Services/QuestionnaireBuilder.php`
- `app/Modules/Questionnaire/Services/QuestionnaireProcessor.php`
- `app/Modules/Questionnaire/Services/QuestionnaireDataMapper.php`
- `app/Modules/Questionnaire/Jobs/ProcessQuestionnaireStepJob.php`
- `app/Observers/UserObserver.php` — triggers processing after registration.

**Tests:** `tests/Feature/Modules/Questionnaire/` — 21 tests, all passing.

### 2. Yandex.Smena Module (`app/Modules/YandexSmena/`)

**Status:** Full lifecycle implemented and tested.

**What it does:**
- Independent module for Yandex.Smena API integration.
- Migrations for sites, professions, payments, shifts, candidates, favorite workers, event log, and poll cursor.
- HTTP client for `publish`, `poll`, and `worker` endpoints with rate limiting.
- Event publisher with idempotency (`event_id`) and monotonic timestamps (`event_ts`).
- Outgoing shift lifecycle events: create, cancel, resume, approve/reject worker, set code, start, set fact, rate worker.
- Worker interaction events: block, unblock, like, unlike.
- Poll command and incoming event handlers for signup, withdraw, adjust fact, and event results.
- Reference data mapping tables for Yandex-provided `site_id`, `profession_id`, `payment_id`.

**Key files:**
- `docs/YANDEX_SMENA_MODULE.md`
- `app/Modules/YandexSmena/Config/config.php`
- `app/Modules/YandexSmena/Services/YandexSmenaApiClient.php`
- `app/Modules/YandexSmena/Services/YandexSmenaEventPublisher.php`
- `app/Modules/YandexSmena/Services/SmenaShiftPublisher.php`
- `app/Modules/YandexSmena/Services/SmenaShiftLifecycleService.php`
- `app/Modules/YandexSmena/Services/SmenaWorkerInteractionService.php`
- `app/Modules/YandexSmena/Services/SmenaEventProcessor.php`
- `app/Modules/YandexSmena/Console/Commands/PollYandexSmenaEventsCommand.php`

**Tests:** `tests/Feature/Modules/YandexSmena/`.

**Next steps:**
1. Wire shift publishing into `OrderActivities` / `TaskActivity` workflows.
2. Build admin UI for mapping management and event log inspection.
3. Add retry/monitoring for permanently failed outgoing events.

### 3. User External Sync (`app/Services/UserExternalSync/`)

**Status:** Complete, tested.

**What it does:**
- Builds structured export data for a user.
- Dispatches `SyncUserToExternalSystemJob` to `external-sync` queue when user finishes registration.
- Provides incoming endpoint `/api/integration/syncUser/`.

**Tests:** `tests/Feature/Jobs/SyncUserToExternalSystemJobTest.php`, `tests/Feature/Integration/UserExternalSyncControllerTest.php`, `tests/Feature/Services/UserExternalSyncServiceTest.php`.

## Recent Infrastructure Changes

- `bootstrap/providers.php` — registered `QuestionnaireServiceProvider`, `YandexSmenaServiceProvider`, `ValidationServiceProvider`.
- `config/horizon.php` — added supervisors `external-sync`, `questionnaire`, `yandex-smena`.
- `composer.json` — added PSR-4 autoload prefix `Modules\` for `app/Modules/`.
- `database/migrations/2025_04_29_134534_add_new_user_group.php` — fixed null role handling so tests don't crash on `RefreshDatabase`.
- `app/Providers/ValidationServiceProvider.php` — extracted custom validation rules from `AppServiceProvider`.
- Custom rule `time_end_on_or_before_date_end` added for `dateActivity.*.timeEnd` validation across 6 order/task request files.

## Documentation

- `AGENTS.md` — detailed agent working rules: Sail usage, plan mode, architecture patterns, model PHPDoc, MCP, testing, post-implementation review.
- `docs/KIMI_GUIDE.md` — concise user-facing interaction guide (points to `AGENTS.md`).
- `docs/SKILLS.md` — available skills inventory.
- `docs/FORM_REGISTRATION.md` — dynamic registration form flow.
- `docs/USER_DATA_ANALYSIS.md` — how `users.data` is stored and analyzed.
- `docs/QUESTIONNAIRE_MODULE.md` — questionnaire module documentation.
- `docs/YANDEX_SMENA_MODULE.md` — Yandex.Smena module documentation.
- `docs/PROJECT_STATUS.md` — this file.

## Active Decisions

1. **Module pattern** — new large features live in `app/Modules/<Name>/` with their own config, migrations, models, providers, routes. Registered in `bootstrap/providers.php`.
2. **Validation provider pattern** — custom validation rules live in `app/Providers/ValidationServiceProvider.php`, not `AppServiceProvider`.
3. **Plan mode** — used for non-trivial/architectural tasks; small fixes done directly with internal plan.
4. **Agents & swarms** — use subagents to parallelize independent exploration or processing tasks.
5. **Queue per module** — each integration has its own Horizon supervisor queue.
6. **Tests** — feature tests with `RefreshDatabase`; PHPUnit via Sail.
7. **Code style** — Laravel Pint run after every change batch.

## Known Pre-existing Issues

- `Tests\Feature\ExampleTest` expects 200 from `/` but project returns 404. Unrelated to all changes.
- Some project models use `HasFactory` without actual factory classes; tests avoid `Model::factory()` for those.

## Useful Commands

```bash
# Run all tests
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit

# Run module tests
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit tests/Feature/Modules/Questionnaire
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit tests/Feature/Modules/YandexSmena

# Pint
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/pint

# Migrations
./vendor/bin/sail exec laravel.test php artisan migrate --force

# Queue worker
./vendor/bin/sail exec laravel.test php artisan horizon
```

## Next Work Items

1. Wire Yandex.Smena shift publishing into `OrderActivities` / `TaskActivity` workflows.
2. Build admin UI for Yandex.Smena mapping management and event log inspection.
3. Add retry/monitoring for permanently failed outgoing events.
4. Document recognition service architecture when requirements are ready.
