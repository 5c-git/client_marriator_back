# Questionnaire Module

Independent module for moderating, validating, and enriching user registration data.

## Location

`app/Modules/Questionnaire/`

## Purpose

The module takes a `User`, builds a structured **questionnaire** from the user's fields, and runs a configurable chain of validation/enrichment steps. Each step is a service implementing a single interface; adding a new check only requires creating the service class and adding it to `config/questionnaire.php`.

## Structure

```text
app/Modules/Questionnaire/
├── Config/
│   └── config.php                  # Step chain and queue name
├── Database/
│   └── Migrations/
│       └── 2025_06_18_000000_create_questionnaires_table.php
├── Enums/
│   └── QuestionnaireStatus.php     # pending/in_progress/completed/failed
├── Exceptions/
│   └── QuestionnaireProcessingException.php
├── Http/
│   └── Controllers/
│       └── QuestionnaireController.php
├── Jobs/
│   └── ProcessQuestionnaireStepJob.php
├── Models/
│   └── Questionnaire.php
├── Providers/
│   └── QuestionnaireServiceProvider.php
├── Routes/
│   └── api.php
└── Services/
    ├── QuestionnaireBuilder.php    # Parses user -> questionnaire data
    ├── QuestionnaireDataMapper.php # Adds semantic keys to data for services
    ├── QuestionnaireProcessor.php  # Orchestrates the step chain
    └── Steps/
        ├── QuestionnaireStepInterface.php
        ├── ValidatePersonalDataStep.php       # example
        ├── CheckExternalRegistryStep.php      # example external call
        └── EnrichGeoDataStep.php              # example enrichment
```

## Field Aliasing

Registration fields are stored in `users.data` under UUID keys. Steps work with semantic keys (`first_name`, `last_name`, `phone`, `inn`, etc.).

`QuestionnaireFieldAlias` enum maps UUIDs to semantic keys. `QuestionnaireDataMapper::map()` returns a copy of the data with semantic keys added, plus a computed `full_name`.

Example inside a step:

```php
$data = $questionnaire->mappedData();
$phone = $data['phone'];
$fullName = $data['full_name'];
```

The original `$questionnaire->data` (UUID keys) is stored unchanged in the database.

## Storage Format

Table `questionnaires`:

| Column | Purpose |
|---|---|
| `user_id` | One active questionnaire per user (unique) |
| `status` | `pending` / `in_progress` / `completed` / `failed` |
| `current_step_index` | Index in the configured step chain |
| `current_step_class` | FQCN of the current/last executed step |
| `data` | Questionnaire payload (JSON) |
| `logs` | History of every executed step |
| `error_message` | Message when status is `failed` |
| `completed_at` / `failed_at` | Timestamps |

`data` shape:

```php
[
    'user_id' => 184,
    'phone' => '79269453055',
    // base user fields: name, email, img, finishRegister, confirmRegister,
    // pin, uuid, latitude, longitude, mapAddress, mapRadius
    // decoded JSON columns: expansionData, errorData, requisitesData
    // (archive is intentionally not included)
    // all keys from users.data (flat or step-based)
    'registration_fields' => [
        'field-uuid' => [
            'name' => 'Налоговый статус',
            'type' => 'directory',
            'value' => ['uuid' => '...', 'name' => '...'],
        ],
    ],
]
```

Top-level keys are easy to read and update. `registration_fields` adds metadata for registered form fields.

### Complex user fields

- `expansionData` — moderator expansion of uploaded documents, shape `{field-uuid: [{name, value}, ...]}`.
- `errorData` — field-level error messages, shape `{field-uuid: "error message"}`.
- `requisitesData` — array of payment requisites, shape `[{bik, fio, card, account, cardDue, confidant, payWithCard}, ...]`.

## Configuration

`app/Modules/Questionnaire/Config/config.php` (published to `config/questionnaire.php`):

```php
return [
    'steps' => [
        \Modules\Questionnaire\Services\Steps\ValidatePersonalDataStep::class,
        \Modules\Questionnaire\Services\Steps\CheckExternalRegistryStep::class,
        \Modules\Questionnaire\Services\Steps\EnrichGeoDataStep::class,
    ],
    'queue' => env('QUESTIONNAIRE_QUEUE', 'questionnaire'),
];
```

## Step Interface

```php
interface QuestionnaireStepInterface
{
    public function name(): string;
    public function isRequired(): bool;
    public function handle(Questionnaire $questionnaire): void;
}
```

- `isRequired()` — if `true`, a failure stops the whole chain. If `false`, the failure is logged and the next step runs.
- A step should throw `QuestionnaireProcessingException` on expected validation failures.

## Execution Flow

1. `QuestionnaireProcessor::processUser($user)` calls `QuestionnaireBuilder::buildForUser($user)`.
2. Builder creates or updates the single `questionnaires` record for the user.
3. Processor cancels pending jobs for that questionnaire (deduplication/restart).
4. Processor acquires a Redis lock for the questionnaire.
5. `ProcessQuestionnaireStepJob` is dispatched for step index `0`.
6. Each job:
   - marks the questionnaire `in_progress` with current step;
   - runs the step;
   - on success appends a log entry and dispatches the next step;
   - on expected failure (`QuestionnaireProcessingException`):
     - if the step is **required** (`isRequired() === true`), marks the questionnaire `failed`;
     - if the step is **optional** (`isRequired() === false`), logs the failure and dispatches the next step.
7. Lock is released on terminal state (`completed` or `failed`).

## API

| Method | Route | Description |
|---|---|---|
| POST | `/api/questionnaire/start/{user}` | Build/update questionnaire and start processing |
| GET | `/api/questionnaire/status/{user}` | Get current status and step |
| GET | `/api/questionnaire/result/{user}` | Get full data and logs |

## Usage in Code

### Manual trigger

```php
use Modules\Questionnaire\Services\QuestionnaireProcessor;

$processor = app(QuestionnaireProcessor::class);
$processor->processUser($user);
```

### Automatic trigger after registration

`UserObserver::updated()` starts questionnaire processing right after `SyncUserToExternalSystemJob::dispatchForUser($user)` when `finishRegister` and `confirmRegister` become `true`.

## Queue & Horizon

The module uses the `questionnaire` queue. A Horizon supervisor is added in `config/horizon.php`.

## Tests

Tests live in `tests/Feature/Modules/Questionnaire/`.

Run only module tests:

```bash
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit tests/Feature/Modules/Questionnaire
```

## Adding a New Step

1. Create a class in `app/Modules/Questionnaire/Services/Steps/`.
2. Implement `QuestionnaireStepInterface`.
3. Add the FQCN to `config/questionnaire.php` in the desired position.

No other code changes are required.
