# Kimi Guide — Marriator

This is a short guide for interacting with Kimi Code CLI on this project. For detailed agent instructions (commands, architecture, testing, etc.), see `AGENTS.md`.

## Quick Commands

All commands run through Laravel Sail:

```bash
# Run tests
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit

# Check / fix code style
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/pint

# Tinker
./vendor/bin/sail exec laravel.test php artisan tinker
```

## Working with Kimi

- **Automatic mode.** Kimi will choose the right workflow for each task. For non-trivial changes it enters plan mode automatically and presents the plan for approval.
- **Planning is required** for new features, architectural decisions, multi-file changes, or unclear requirements.
- **Context.** Kimi tracks progress in the TODO list and reads `AGENTS.md` and the docs listed there before starting work.
- **MCP.** Kimi can use the Laravel Boost MCP server for database schema, queries, docs search, and application info.

## Key Documentation

- `docs/FORM_REGISTRATION.md` — registration form flow
- `docs/USER_DATA_ANALYSIS.md` — `users.data` structure
- `docs/QUESTIONNAIRE_MODULE.md` — questionnaire module
- `docs/YANDEX_SMENA_MODULE.md` — Yandex.Smena integration
- `docs/PROJECT_STATUS.md` — current project snapshot
- `docs/SKILLS.md` — available skills

## Module Pattern

Large features are built as modules under `app/Modules/<ModuleName>/` with their own config, migrations, models, routes, and provider. If you want a new module, ask Kimi to plan it first.

## Asking for Help

- Be specific about what should happen and how to verify it.
- If something is unclear, Kimi will ask before proceeding.
