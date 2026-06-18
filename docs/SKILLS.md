# Список Laravel skills проекта

Все skills находятся в директории `.kimi-code/skills/`. Каждый skill — это файл `SKILL.md` с инструкциями для AI-ассистента.

**Всего skills:** 55

---

## Мета-навыки и workflow

| Skill | Назначение |
|---|---|
| `using-laravel-superpowers` | Главный skill. Описывает runner selection, core workflows и как применять Laravel skills без привязки к платформе. |
| `brainstorming` | Интерактивное проектирование фич с уточнением домена, данных, интерфейсов, тестирования и quality gates. |
| `writing-plans` | Как формализовать план реализации перед написанием кода. |
| `executing-plans` | Выполнение уже готового плана пошагово. |
| `daily-workflow` | Рекомендации по ежедневной работе с проектом. |
| `effective-context` | Как эффективно передавать контекст AI. |
| `prompt-structure` | Структура хороших запросов. |
| `using-examples-in-prompts` | Как использовать примеры в промптах. |
| `specifying-constraints` | Как задавать ограничения и требования. |

---

## Качество кода и рефакторинг

| Skill | Назначение |
|---|---|
| `quality-checks` | Laravel Pint, PHPStan, Insights и другие quality gates. |
| `complexity-guardrails` | Контроль сложности кода. |
| `code-review-requests` | Как запрашивать и проводить code review. |
| `iterating-on-code` | Итеративная доработка кода. |
| `controller-cleanup` | Очистка и упрощение контроллеров. |
| `debugging-prompts` | Отладка через правильные запросы. |
| `documentation-best-practices` | Лучшие практики документирования кода. |

---

## Laravel-специфика

| Skill | Назначение |
|---|---|
| `laravel-best-practices` | Общие best practices Laravel. |
| `laravel-prompting-patterns` | Паттерны запросов специфичные для Laravel. |
| `routes-best-practices` | Лучшие практики работы с маршрутами. |
| `form-requests-and-validation` | Form Request'ы и валидация. |
| `eloquent-relationships-and-loading` | Связи Eloquent и eager loading. |
| `migrations-and-factories` | Миграции, фабрики и seeders. |
| `blade-components-and-layouts` | Blade-компоненты и layouts. |
| `custom-helpers` | Создание хелперов проекта. |
| `config-env-storage` | Работа с config, env и storage. |
| `constants-and-configuration` | Константы и конфигурация приложения. |
| `exception-handling-and-logging` | Обработка исключений и логирование. |
| `filesystem-uploads-and-urls` | Работа с файлами, загрузками и URL. |
| `internationalization-and-translation` | Локализация и переводы. |
| `task-scheduling` | Планировщик зада Laravel. |

---

## База данных и производительность

| Skill | Назначение |
|---|---|
| `performance-eager-loading` | Оптимизация через eager loading. |
| `performance-select-columns` | Оптимизация выборки колонок. |
| `performance-caching` | Кеширование в Laravel. |
| `data-chunking-large-datasets` | Работа с большими наборами данных по chunk'ам. |
| `transactions-and-consistency` | Транзакции и консистентность данных. |

---

## Архитектурные паттерны

| Skill | Назначение |
|---|---|
| `ports-and-adapters` | Архитектура Ports & Adapters. |
| `strategy-pattern` | Паттерн Стратегия. |
| `template-method-and-plugins` | Template Method и плагины. |
| `interfaces-and-di` | Интерфейсы и Dependency Injection. |

---

## API и HTTP

| Skill | Назначение |
|---|---|
| `api-resources-and-pagination` | API Resources и пагинация. |
| `api-surface-evolution` | Эволюция API без ломания обратной совместимости. |
| `rate-limiting-and-throttle` | Rate limiting и throttle. |
| `http-client-resilience` | Отказоустойчивость HTTP-клиента. |

---

## Тестирование

| Skill | Назначение |
|---|---|
| `tdd-with-pest` | Разработка через тестирование с Pest. |
| `controller-tests` | Тестирование контроллеров. |
| `e2e-playwright` | End-to-end тесты с Playwright. |
| `bootstrap-check` | Проверка окружения перед запуском. |

---

## Авторизация и безопасность

| Skill | Назначение |
|---|---|
| `policies-and-authorization` | Политики и авторизация. |

---

## Queues и Horizon

| Skill | Назначение |
|---|---|
| `queues-and-horizon` | Работа с очередями и Horizon. |
| `configuring-horizon` | Настройка Horizon. |
| `horizon-metrics-and-dashboards` | Метрики и дашборды Horizon. |

---

## Деплой и инфраструктура

| Skill | Назначение |
|---|---|
| `deploying-laravel-cloud` | Деплой на Laravel Cloud. |
| `runner-selection` | Выбор между Sail и хост-окружением. |

---

## Прочее

| Skill | Назначение |
|---|---|
| `nova-resource-patterns` | Паттерны ресурсов Laravel Nova. |
| `dependencies-trim-packages` | Оптимизация и чистка зависимостей. |

---

## Как активировать skill

```text
/laravel:using-laravel-superpowers
/laravel:brainstorming
/laravel:quality-checks
/superpowers-laravel:brainstorm
/superpowers-laravel:write-plan
/superpowers-laravel:execute-plan
```

Или просто опиши задачу — AI сам подберёт подходящий skill.

---

## Boost skills

В `boost.json` дополнительно указаны skills от Laravel Boost:

- `laravel-best-practices`
- `configuring-horizon`
- `deploying-laravel-cloud`

Полный набор доступных Boost-skills находится в `vendor/laravel/boost/.ai/`.
