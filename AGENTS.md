# AI Agent Instructions — Marriator

This file guides Kimi Code CLI when working on this Laravel project.

<foundation-rules>

## Project Overview

- **PHP:** 8.3
- **Framework:** Laravel 11.54.0
- **Environment:** Laravel Sail (Docker) with MySQL, Redis, Meilisearch, Mailpit, Selenium
- **Package Manager:** Composer
- **Frontend build:** Vite
- **Testing:** PHPUnit 11
- **Key packages:**
  - `laravel/horizon` v5
  - `laravel/passport` v12
  - `laravel/sanctum` v4
  - `laravel/boost` v2
  - `laravel/mcp` v0
  - `laravel/pint` v1
  - `laravel/sail` v1
  - `phpunit/phpunit` v11

> For version-specific Laravel docs and examples, use the `search-docs` MCP tool or check `CLAUDE.md`.

## Key Documentation

Read these files explicitly when the task touches the related domain:

- `docs/FORM_REGISTRATION.md` — dynamic user registration form flow.
- `docs/USER_DATA_ANALYSIS.md` — how `users.data` is stored and analyzed.
- `docs/QUESTIONNAIRE_MODULE.md` — questionnaire module: step chain, queue, API.
- `docs/YANDEX_SMENA_MODULE.md` — Yandex.Smena integration module.
- `docs/PROJECT_STATUS.md` — snapshot of completed work, decisions, next items.
- `docs/SKILLS.md` — inventory of available Laravel skills.
- `docs/KIMI_GUIDE.md` — user-facing guide for interacting with Kimi.

## Skills Activation

- This project has domain-specific skills in `.kimi-code/skills/`.
- **Always activate the relevant skill** when working in that domain — do not wait until stuck.
- Default meta-skill: `laravel:using-laravel-superpowers`.

</foundation-rules>

<working-mode>

## Working Mode

- Use the **`laravel:using-laravel-superpowers`** methodology by default.
- Work in **automatic mode**: choose the right workflow based on task complexity.
- **Plan mode** — use proactively and often:
  - For new features, architectural decisions, multi-file changes, unclear requirements, or anything non-trivial, enter plan mode first.
  - Thoroughly explore the codebase and think through architecture before proposing a solution.
  - Present the plan to the user for approval before writing code.
  - If difficulties or unclear requirements appear during execution, pause and ask.
- **Skip user approval for truly small tasks** — a couple of files, no full rewrite, obvious fix — but still write a short internal plan for yourself.
- The user will slow down or correct the process when needed.
- Be concise in explanations — focus on what's important, avoid stating obvious details.

## Agent & Swarm Usage

Use subagents to parallelize independent work, not for sequential reasoning.

- **`Agent(subagent_type="explore")`** — for codebase exploration that needs more than 3 searches or spans multiple files/topics. Read-only.
- **`Agent(subagent_type="coder")`** — for focused non-trivial implementation subtasks. Pass the full context; the subagent starts with zero history.
- **`AgentSwarm`** — when the same task must be applied to many independent items (review N files, process N modules, etc.). Use a `{{item}}` template. Max 128 subagents.
- Do **not** use agents for trivial one-file reads or edits — direct tools are faster.
- Prefer resuming an existing agent over spawning a new one when continuing its work.

</working-mode>

<commands>

## Command Runner Selection

**All commands MUST run through Laravel Sail.** Do not use host PHP/Composer/Node unless Sail is explicitly unavailable.

```bash
# Artisan
./vendor/bin/sail exec laravel.test php artisan <command>

# Composer
./vendor/bin/sail composer <command>

# Node / Vite
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build

# Tests
./vendor/bin/sail artisan test --compact
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit tests/Feature/Modules/Questionnaire

# Pint
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/pint
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/pint app/Http/Requests/Order
```

- Pass `--no-interaction` to Artisan commands when running non-interactively.
- Use `./vendor/bin/sail` without arguments to list available Sail commands.

</commands>

<frontend>

## Frontend Bundling

If a frontend change is not reflected in the UI:

```bash
./vendor/bin/sail npm run build
./vendor/bin/sail npm run dev
./vendor/bin/sail composer run dev
```

Vite manifest errors are usually fixed by `npm run build`.

</frontend>

<code-style>

## Code Style & Quality

- Follow **PSR-12**.
- Run **Laravel Pint** on changed files before finalizing.
- Use explicit **type hints** and return types.
- Favor small, testable services over fat controllers/jobs/commands.
- Use DTOs, typed Collections, and Enums when they clarify intent.
- Prefer model factories in tests and model scopes for complex queries.
- Always use curly braces for control structures, even single-line bodies.
- Use PHP 8 **constructor property promotion**.
- Use **TitleCase** for Enum keys.
- Prefer **PHPDoc blocks** over inline comments; use array shape type definitions in PHPDoc.
- Use descriptive names: `isRegisteredForDiscounts`, not `discount()`.

</code-style>

<architecture>

## Architecture

- Controllers are thin: validate input, delegate to services, return responses.
- Business logic lives in **Services** (`app/Services/`).
- Data access and relationships live in **Models** (`app/Models/`).
- Validation lives in **Form Requests** (`app/Http/Requests/`).
- Use Eloquent relationships and eager loading to avoid N+1 queries.
- Stick to the existing directory structure; do not create new base folders without approval.
- Do not change application dependencies without approval.
- Check for existing components to reuse before writing new ones.
- Follow existing code conventions; check sibling files for structure, approach, and naming.
- For APIs, follow existing application convention.
- Prefer named routes and the `route()` helper.

### Module Pattern

Large, loosely-coupled features live in `app/Modules/<ModuleName>/`:

```text
app/Modules/<ModuleName>/
├── Config/config.php
├── Database/Migrations/
├── Http/Controllers/
├── Jobs/
├── Models/
├── Providers/<ModuleName>ServiceProvider.php
├── Routes/api.php
└── Services/
```

Each module has its own service provider that loads routes, migrations, config, and commands. Register new providers in `bootstrap/providers.php`. Add a dedicated Horizon queue for modules that process jobs.

### Validation Provider Pattern

Custom validation rules should not live in `AppServiceProvider::boot()`. Instead:

1. Create `app/Providers/ValidationServiceProvider.php`.
2. Register `Validator::extend(...)` and `Validator::replacer(...)` there.
3. Register the provider in `bootstrap/providers.php`.

This keeps validation logic discoverable and prevents `AppServiceProvider` from growing.

</architecture>

<laravel-11>

## Laravel 11 Structure

- Middleware are registered in `bootstrap/app.php` via `Application::configure()->withMiddleware()`.
- `bootstrap/providers.php` contains application-specific service providers.
- There is no `app/Console/Kernel.php`; commands auto-register.
- Use `vendor/bin/sail artisan make:enum`, `make:class`, `make:interface` for new generic PHP files.
- When creating a new model, also create useful factories and seeders; ask if other related files are needed.

</laravel-11>

<database>

## Database

- All schema changes through **migrations**.
- Use **factories** and **seeders** for test data.
- When modifying a column, include **all previously defined attributes**.
- Laravel 11 supports limiting eagerly loaded records natively: `$query->latest()->limit(10);`.
- Define model casts in a `casts()` method when the project uses that convention.
- Add a PHPDoc block at the top of every Eloquent model documenting properties and relationships.

### Model PHPDoc

Use `@property` for columns and `@property-read` for relations. Example:

```php
/**
 * @property int $id
 * @property int $place_id
 * @property string $external_id
 * @property bool $self_employed
 * @property OrderStatusEnum $status
 * @property-read User $user
 * @property-read Place $place
 * @property-read Collection|OrderActivities[] $orderActivities
 */
class Order extends Model
```

Rules:
- Include significant columns.
- Use correct PHP types (`int`, `string`, `bool`, `array`, `Carbon\Carbon`, enum class).
- JSON columns → `array`.
- Timestamps → `Carbon\Carbon`.
- Relations → `@property-read`; `Collection|Model[]` for `hasMany`, single model for `belongsTo`/`hasOne`.
- Update the block when schema or relations change.

### Direct MySQL / PHP Access

```bash
# MySQL query
./vendor/bin/sail mysql -e "SELECT * FROM users LIMIT 5;"

# PHP one-liner
./vendor/bin/sail exec laravel.test php artisan tinker --execute='echo App\Models\User::count();'
```

### Tinker Rules

- Use Tinker for debugging and testing code snippets.
- Do **not** create models without user approval; prefer tests with factories.
- Prefer existing Artisan commands over custom Tinker code.
- Always use **single quotes** around the `--execute` argument to prevent shell expansion:
  ```bash
  ./vendor/bin/sail artisan tinker --execute='User::where("active", true)->count();'
  ```

</database>

<mcp>

## MCP (Model Context Protocol)

Laravel Boost MCP server runs via `./vendor/bin/sail artisan boost:mcp`.

Useful tools:

| Tool | Purpose |
|---|---|
| `application-info` | PHP/Laravel/package versions |
| `database-schema` | Tables, columns, indexes, foreign keys |
| `database-query` | Read-only SQL |
| `search-docs` | Version-specific Laravel docs |
| `browser-logs` | Recent browser logs and errors |
| `get-absolute-url` | Resolve correct project URLs |

Prefer MCP over raw SQL when it gives the same answer faster. For complex tasks, still read the relevant source files directly.

### Known issue

The Sail script may print warnings like:

```text
./.env: line 75: 45LMSjPbrnLtOJtuwma4cnZO857AGVEnxVaowdTz: command not found
```

These come from `.env` values containing shell-special characters. They do not block execution, but should be fixed by properly quoting those values when convenient.

</mcp>

<redis>

## Redis

Redis is used for queues and Horizon. Inspect via Sail:

```bash
# Connectivity
./vendor/bin/sail exec redis redis-cli PING

# Queue length and contents
./vendor/bin/sail exec redis redis-cli LLEN queues:default
./vendor/bin/sail exec redis redis-cli LRANGE queues:default 0 -1

# Horizon failed jobs
./vendor/bin/sail exec redis redis-cli LRANGE laravel_horizon:recent_failed_jobs 0 -1

# Horizon status
./vendor/bin/sail artisan horizon:list
./vendor/bin/sail artisan horizon:status

# Failed jobs list
./vendor/bin/sail artisan queue:failed
```

</redis>

<testing>

## Testing

- This project uses **PHPUnit 11**.
- Write tests for new features and bug fixes.
- Use factories for test models when they exist; otherwise create models directly.
- Do not remove any tests or test files without approval.
- If you encounter Pest-style tests, convert them to PHPUnit.
- Create tests via Artisan: `./vendor/bin/sail artisan make:test --phpunit {name}`.
- Cover happy paths, failure paths, and edge cases.

```bash
# All tests
./vendor/bin/sail artisan test --compact
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit

# Filter
./vendor/bin/sail exec -u sail laravel.test ./vendor/bin/phpunit --filter=testName
```

</testing>

<review>

## Post-Implementation Review

After writing or modifying code, perform a self-review before considering the task done.

1. **Correctness** — does the change do what was asked? Happy/failure/edge cases handled?
2. **Consistency** — follows existing patterns? No duplicate logic?
3. **Performance** — no N+1, no unnecessary API/DB calls?
4. **Bugs & regressions** — null pointers, type mismatches, broken related features?
5. **Test coverage** — new code tested, existing tests pass?
6. **Style** — Pint passes, no debug code or unused imports?

If any check reveals an issue, fix it and re-check. Do not finish with known issues or `TODO` without explicit user approval.

## Before Completing a Task

1. Run tests — they must pass.
2. Run Laravel Pint — code must be formatted.
3. If applicable, run static analysis (`phpstan` if configured).
4. Summarize what was changed and why.

</review>

<documentation>

## Documentation

- Project docs live in `docs/`.
- Feature/module docs requested or co-created with the user → `docs/`.
- Internal agent guidelines, checklists, process notes → `docs/agent/`.
- Project snapshots and status docs may live in `docs/` when useful for both user and agent.
- Update relevant docs when changing conventions, modules, or architecture.

</documentation>

<security>

## Security & Safety

- Do not modify `.env`, SSH keys, or credential files.
- Do not run `git commit`, `git push`, `git reset`, or `rm -rf` without explicit user confirmation.
- Back up config files before editing.

</security>
