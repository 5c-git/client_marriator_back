# Marriator

Laravel 11 REST API + admin panel for managing orders, tasks, specialist bids, reports, and payments.

## Stack

- PHP 8.3
- Laravel 11
- Laravel Sail (Docker): MySQL, Redis, Meilisearch, Mailpit, Selenium
- Laravel Horizon, Passport, Sanctum, Pint
- PHPUnit 11

## Quick Start

```bash
# Start environment
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail exec laravel.test php artisan migrate

# Run tests
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit

# Run Pint
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/pint
```

## Documentation

- `AGENTS.md` — instructions for AI agents working on this project.
- `docs/DOCUMENTATION.md` — project overview, routes, models, business logic.
- `docs/FORM_REGISTRATION.md` — dynamic registration form flow.
- `docs/USER_DATA_ANALYSIS.md` — how user data is stored and analyzed.
- `docs/QUESTIONNAIRE_MODULE.md` — post-registration questionnaire module.
- `docs/YANDEX_SMENA_MODULE.md` — Yandex.Smena integration module.
- `docs/PROJECT_STATUS.md` — current project state and next work items.
- `docs/SKILLS.md` — available Laravel skills.

## License

MIT
