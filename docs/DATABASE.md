# Детальная структура базы данных Marriator

Документ описывает все таблицы, колонки, типы и связи по миграциям и моделям.

---

## 1. Пользователи и авторизация

### 1.1 users

| Колонка | Тип | Nullable | Индекс | Описание |
|---------|-----|----------|--------|----------|
| id | bigint unsigned | NO | PK, AI | Первичный ключ |
| name | string | YES | — | Имя пользователя |
| email | string | NO | unique | Email |
| email_verified_at | timestamp | YES | — | Дата верификации email |
| password | string | NO | — | Хеш пароля |
| remember_token | string | YES | — | Токен "запомнить меня" |
| created_at | timestamp | YES | — | |
| updated_at | timestamp | YES | — | |
| api_token | string(60) | YES | unique | API-токен (legacy) |
| phone | bigInteger | YES | index | Телефон |
| data | json/text | YES | — | Данные анкеты (JSON по шагам) |
| img | string | YES | — | Путь к фото пользователя |
| confirmRegister | boolean | YES | — | Регистрация подтверждена (модерация) |
| pin | string | YES | — | PIN-код для входа в ЛК |
| finishRegister | boolean | YES | — | Анкета заполнена до конца |
| expansionData | json | YES | — | Доп. данные |
| errorData | json | YES | — | Ошибки/данные ошибок |
| estateData | json | YES | — | Данные недвижимости |
| requisitesData | json | YES | — | Реквизиты |
| mapAddress | string | YES | — | Адрес на карте |
| mapRadius | int | YES | — | Радиус на карте (значение из справочника) |
| updateData | json | YES | — | Данные для обновления |
| change_fields | json | YES | — | Изменённые поля |
| date_for_send | date/datetime | YES | — | Дата для отправки |
| uuid | string | YES | — | UUID из 1С / внешней системы |
| register_hash | string | YES | — | Хеш ссылки для регистрации |
| change_order, cancel_order, live_order | bool | YES | — | Флаги уведомлений заказов |
| change_task, cancel_task, live_task | bool | YES | — | Флаги уведомлений задач |
| repeat_bid, leave_bid, refusal_task, waiting_task | bool | YES | — | Флаги уведомлений ставок/задач |
| latitude | decimal | YES | — | Широта |
| longitude | decimal | YES | — | Долгота |
| count_wait_bid | int | YES | — | Количество ожидающих ставок |
| time_answer_bid | int | YES | — | Время ответа на ставку |
| notification_start | bool | YES | — | Уведомление о старте |
| verme_id | bigInteger | YES | — | ID в Verme (PVP) |
| nopaper_guid | string | YES | — | GUID в Nopaper |
| nopaper_certificate_id | string | YES | — | ID сертификата Nopaper |
| time_book_guid | string | YES | — | GUID в TimeBook (PVP) |

### 1.2 password_reset_tokens

| Колонка | Тип | Описание |
|---------|-----|----------|
| email | string | PK |
| token | string | Токен сброса |
| created_at | timestamp | |

### 1.3 sessions

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | string | PK |
| user_id | bigint unsigned | nullable, index |
| ip_address | string(45) | |
| user_agent | text | |
| payload | longText | |
| last_activity | int | index |

### 1.4 roles

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint unsigned | PK, AI |
| name | string | Имя роли (admin, client, manager, recruiter, supervisor, specialist) |
| created_at, updated_at | timestamp | |

### 1.5 user_roles (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| user_id | int | |
| role_id | int | |

### 1.6 personal_access_tokens (Sanctum)

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| tokenable_type | string | |
| tokenable_id | bigint | |
| name | string | |
| token | string | hash |
| abilities | text | |
| last_used_at | timestamp | |
| expires_at | timestamp | |
| created_at, updated_at | timestamp | |

### 1.7 OAuth (Laravel Passport)

- **oauth_auth_codes** — коды авторизации
- **oauth_access_tokens** — access-токены (id, user_id, client_id, name, scopes, revoked, expires_at и др.)
- **oauth_refresh_tokens** — refresh-токены
- **oauth_clients** — OAuth-клиенты
- **oauth_personal_access_clients** — клиенты для personal access

---

## 2. Поля форм и справочники

### 2.1 fields

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint | PK, AI | |
| uuid | string | NO | index |
| name | string | NO | |
| description | text | YES | |
| parentFields | json | YES | Условия показа (родительские поля) |
| type | smallInteger | NO | Тип поля (FieldsTypeEnum) |
| directory | string | YES | Имя справочника для типа select |
| active | boolean | default false | index |
| step | smallInteger | YES | Шаг регистрации |
| sort | int | YES | Сортировка |
| required | boolean | YES | Обязательное поле |
| section | string | YES | Секция (личные данные и т.д.) |
| estate | boolean | YES | Поле недвижимости |
| requisites | boolean | YES | Поле реквизитов |
| screen | text | YES | Экран отображения |
| role_id | bigInteger | YES | Привязка к роли |
| label, heading, placeholder | text | YES | Подписи |
| helperInfo_*, drawerInfo_* | text | YES | Подсказки |
| dividerTop, dividerBottom | bool | YES | Разделители |

### 2.2 fields_user_role (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| field_id | bigint unsigned | index |
| role_id | bigint unsigned | index |

### 2.3 Справочники (directory_*)

Общая структура у большинства: **id**, **name**, **uuid** (где есть), **parentFields** (json), **active** (bool). Ниже — отличия.

| Таблица | Дополнительные колонки |
|---------|------------------------|
| directory_country | description, parentFields, active |
| directory_bank | uuid, bic, description, parentFields, fields (json), active |
| directory_activities | detail_img, preview_text и др. |
| directory_tax_status | |
| directory_citizenship | |
| directory_residence | |
| directory_region_of_residence | |
| directory_offer_search | |
| directory_view_activities | preview_text, traveling (bool), standard, self_employed (bool), price |
| directory_weight, directory_height, directory_shoe_size, directory_clothing_size | |
| directory_hair_color, directory_hair_length, directory_gender | |
| directory_messengers | |
| directory_documentation | |
| directory_organization | counterparty_id (FK) |
| directory_age, directory_medical_book | |
| directory_project | uuid, name, date_start (datetime) |
| directory_brand | uuid, name, logo, description |
| directory_counterparty | uuid, name, inn, ogrn, legal_address, legal_email, brand_name, position, web, kpp, bank (реквизиты) |
| directory_place | uuid, brand_id (FK), name, address_kladr, latitude, longitude, verme_id |
| directory_standard | |
| directory_radius | value, default (bool) |
| directory_reasons | name, amount (int) |

### 2.4 Связи многие-ко-многим (справочники)

| Таблица | Колонки | Описание |
|---------|---------|----------|
| directory_brand_directory_counterparty | brand_id, counterparty_id | PK (brand_id, counterparty_id) |
| directory_project_directory_counterparty | project_id, counterparty_id | PK |
| directory_project_directory_place | project_id, place_id | PK |
| directory_project_directory_view_activities | project_id, view_activities_id | PK, price (int) |

### 2.5 Связи пользователь — справочники

| Таблица | Колонки | Описание |
|---------|---------|----------|
| user_directory_project | user_id, project_id (или directory_id) | Pivot пользователь–проект |
| user_directory_place | user_id, place_id | Pivot пользователь–место |
| user_directory_counterparty | user_id, counterparty_id | Pivot пользователь–контрагент |

---

## 3. Заказы, задачи, ставки, запросы

### 3.1 orders

| Колонка | Тип | Nullable | Индекс | Описание |
|---------|-----|----------|--------|----------|
| id | bigint | PK, AI | |
| place_id | bigInteger | NO | index | Место (directory_place) |
| user_id | bigInteger | NO | index | Клиент |
| self_employed | boolean | default false | |
| status | smallInteger | YES | | OrderStatusEnum |
| accept_user_id | bigInteger | YES | index | Менеджер, принявший заказ |
| external_id | string | YES | | ID во внешней системе (PVP) |
| external_type | smallInteger | YES | index | Тип PVP |
| created_at, updated_at | timestamp | YES | |

### 3.2 order_activities

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| order_id | bigint unsigned | index, NOT NULL |
| view_activity_id | bigint unsigned | index, NOT NULL |
| count | int | NOT NULL |
| date_start | dateTime | NOT NULL |
| date_end | dateTime | NOT NULL |
| need_foto | boolean | NOT NULL |
| date_activity | json | YES |

### 3.3 accept_order (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| order_id | bigint unsigned | PK, index |
| user_id | bigint unsigned | PK, index |

### 3.4 tasks

| Колонка | Тип | Nullable | Индекс | Описание |
|---------|-----|----------|--------|----------|
| id | bigint | PK, AI | |
| place_id | bigInteger | NO | index | |
| user_id | bigInteger | NO | index | Менеджер (владелец) |
| accept_user_id | bigInteger | NO | index | Кто принял заказ |
| specialist_user_id | bigInteger | YES | index | Назначенный специалист |
| order_id | bigInteger | NO | index | Исходный заказ |
| project_id | bigInteger | YES | index | Проект |
| status | smallInteger | NO | default 1 | OrderStatusEnum |
| self_employed | boolean | default false | |
| price | decimal(10,2) | NO | |
| income | decimal(10,2) | NO | |
| scope_of_services | decimal(10,2) | NO | |
| external_id | string | YES | |
| external_type | smallInteger | YES | index |
| created_at, updated_at | timestamp | YES | |

### 3.5 task_activities

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| task_id | bigint unsigned | index, NOT NULL |
| view_activity_id | bigint unsigned | index, NOT NULL |
| count | int | NOT NULL |
| date_start | dateTime | NOT NULL |
| date_end | dateTime | NOT NULL |
| need_foto | boolean | NOT NULL |
| date_activity | json | YES |

### 3.6 accept_task (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| task_id | bigint unsigned | PK, index |
| user_id | bigint unsigned | PK, index |
| accepted | boolean | default false |

### 3.7 bids

| Колонка | Тип | Nullable | Индекс | Описание |
|---------|-----|----------|--------|----------|
| id | bigint | PK, AI | |
| place_id | bigInteger | NO | index | |
| user_id | bigInteger | NO | index | Специалист (автор ставки) |
| accept_user_id | bigInteger | YES | index | Менеджер/супервайзер |
| order_id | bigInteger | YES | index | |
| task_id | bigInteger | YES | index | |
| status | smallInteger | NO | default 1 | OrderStatusEnum |
| self_employed | boolean | default false | |
| radius | int | YES/NO | | Радиус (справочник) |
| price | decimal(10,2) | NO | |
| view_activity_id | bigint unsigned | NO | index | Вид активности |
| count | int | YES | |
| date_start | dateTime | YES | |
| date_end | dateTime | YES | |
| need_foto | boolean | YES | |
| date_activity | json | YES | |
| activity_id | bigInteger | YES | index | |
| external_id | string | YES | |
| external_type | smallInteger | YES | index |
| created_at, updated_at | timestamp | YES | |

*Примечание:* колонка supervisor_user_id удалена в одной из миграций.

### 3.8 bid_activities

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| bid_id | bigint unsigned | index, NOT NULL |
| view_activity_id | bigint unsigned | index, NOT NULL |
| count | int | NOT NULL |
| date_start | dateTime | NOT NULL |
| date_end | dateTime | NOT NULL |
| need_foto | boolean | NOT NULL |
| date_activity | json | YES |

### 3.9 accept_bid (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| bid_id | bigint unsigned | PK, index |
| user_id | bigint unsigned | PK, index |
| accepted | boolean | default false |
| task_id | bigInteger | YES | |
| order_id | bigInteger | YES | |
| user_id_maintainer | bigInteger | YES | Супервайзер/менеджер |
| count | int | YES | (добавлено позже) |
| created_at, updated_at | timestamp | YES | |

### 3.10 search_request

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint unsigned | PK, AI |
| place_id | bigint | NOT NULL |
| user_id | bigint | NOT NULL | index |
| order_id | bigint | YES | index |
| task_id | bigint | YES | index |
| status | smallint | NOT NULL default 0 |
| self_employed | boolean | NOT NULL default false |
| radius | int | YES |
| price | decimal(10,2) | YES |
| activity_id | bigint | YES | index |
| view_activity_id | bigint unsigned | NOT NULL | index |
| count | int | NOT NULL |
| date_start | dateTime | NOT NULL |
| date_end | dateTime | NOT NULL |
| need_foto | boolean | NOT NULL |
| date_activity | json | YES |
| created_at, updated_at | timestamp | YES |

### 3.11 requests (заявки специалистов)

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| place_id | bigInteger | NOT NULL | index |
| user_id | bigInteger | NOT NULL | index |
| accept_user_id | bigInteger | YES | index |
| order_id | bigInteger | YES | index |
| task_id | bigInteger | YES | index |
| status | smallInteger | NOT NULL default 1 |
| self_employed | boolean | default false |
| radius | int | YES |
| price | decimal(10,2) | YES |
| view_activity_id | bigint unsigned | NOT NULL | index |
| count | int | NOT NULL |
| date_start | dateTime | NOT NULL |
| date_end | dateTime | NOT NULL |
| need_foto | boolean | NOT NULL |
| date_activity | json | YES |
| activity_id | bigInteger | YES | index |
| created_at, updated_at | timestamp | YES |

---

## 4. Отчёты

### 4.1 report

| Колонка | Тип | Nullable | Индекс | Описание |
|---------|-----|----------|--------|----------|
| id | bigint | PK, AI | |
| user_id | bigint unsigned | NO | index | Специалист |
| bid_id | bigint unsigned | NO | index | |
| order_id | bigint unsigned | YES | index | |
| task_id | bigint unsigned | YES | index | |
| date_start | dateTime | YES | index | |
| date_end | dateTime | YES | |
| status | tinyInteger | YES | default 0 | ReportStatusEnum |
| report | json | YES | Содержимое отчёта |
| date_auto_close | dateTime | YES | Автозакрытие |
| dayActivity | int | YES | |
| forPay | float | default 0 | К оплате |
| income | decimal | YES | |
| coefficient | float | YES | Коэффициент |
| hours | decimal(10,2) | YES | default 1 | Часы |
| placeholder | text | YES | |
| pvp | boolean | default false | Учёт из PVP |
| created_at, updated_at | timestamp | YES | |

### 4.2 report_reason (pivot)

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| report_id | bigint | index |
| reason_id | bigint | index |
| count | int | NOT NULL default 1 |
| amount | decimal/int | YES |

---

## 5. Документы

### 5.1 documents

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint | PK, AI | |
| uuid | string | YES | index |
| user_id | bigInteger | YES | index |
| file_path | string | YES | |
| file_name | string | YES | |
| status | string | YES | index | DocumentStatusEnum |
| status_signature | string | YES | DocumentStatusSignatureEnum |
| date_signature | dateTime | YES | |
| document_id | string | YES | ID в Nopaper |
| file_id | string | YES | ID файла в Nopaper |
| file_path_signed | string | YES | Путь к подписанному файлу |
| created_at, updated_at | timestamp | YES | (если добавлены) |

### 5.2 document_templates

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| number | string | YES |
| date_start | date | YES |
| date_end | date | YES |
| ... | | Остальные поля по шаблону |
| created_at, updated_at | timestamp | YES |

### 5.3 recognition_documents

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| ... | | Поля распознавания |
| file_type | tinyInteger | YES |

---

## 6. Прочие таблицы

### 6.1 settings

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| key | string | NOT NULL | (index, может быть снят в миграции) |
| value | string | YES | |
| name | string | NO | default '' |

### 6.2 certificates

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| ... | | Поля сертификатов (из миграции add_certificates) |

### 6.3 manager_supervisor

| Колонка | Тип | Описание |
|---------|-----|----------|
| user_id_manager | bigint unsigned | index, NOT NULL |
| user_id_supervisor | bigint unsigned | index, NOT NULL |

### 6.4 manager_specialist

| Колонка | Тип | Описание |
|---------|-----|----------|
| user_id_manager | bigint unsigned | index, NOT NULL |
| user_id_specialist | bigint unsigned | index, NOT NULL |

### 6.5 supervisor_specialist

| Колонка | Тип | Описание |
|---------|-----|----------|
| user_id_supervisor | bigint unsigned | index, NOT NULL |
| user_id_specialist | bigint unsigned | index, NOT NULL |

### 6.6 user_contract_data

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| user_id | bigint | index |
| ... | | Данные контракта |
| created_at, updated_at | timestamp | YES |

### 6.7 user_updates

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigint | PK, AI |
| ... | | Поля обновлений пользователя |
| time | datetime/другое | (add_time) |
| created_at, updated_at | timestamp | YES |

### 6.8 cache, cache_locks, jobs, job_batches, failed_jobs

Стандартные таблицы Laravel для кеша и очередей.

---

## 7. Модульные таблицы

### 7.1 questionnaires

Таблица модуля анкетирования (`app/Modules/Questionnaire/`).

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| user_id | bigint unsigned | NO | unique | Владелец анкеты |
| status | string | NO | | `pending` / `in_progress` / `completed` / `failed` |
| current_step_index | int | YES | | Индекс текущего шага |
| current_step_class | string | YES | | FQCN текущего/последнего шага |
| data | json | YES | | Данные анкеты |
| logs | json | YES | | История выполнения шагов |
| error_message | string | YES | | Сообщение об ошибке при `failed` |
| completed_at | timestamp | YES | | |
| failed_at | timestamp | YES | | |
| created_at / updated_at | timestamp | YES | | |

### 7.2 yandex_smena_sites

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| place_id | bigint unsigned | NO | Ссылка на `directory_place` |
| external_id | string | YES | | ID площадки в Yandex.Smena |
| payload | json | YES | | Сырые данные, отправленные/полученные |
| created_at / updated_at | timestamp | YES | | |

### 7.3 yandex_smena_professions

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| view_activity_id | bigint unsigned | NO | Ссылка на `directory_view_activities` |
| external_id | string | YES | | ID профессии в Yandex.Smena |
| payload | json | YES | | |
| created_at / updated_at | timestamp | YES | | |

### 7.4 yandex_smena_payments

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| code | string | NO | | Локальный код тарифа (`PAY_100`) |
| external_id | string | YES | | ID тарифа в Yandex.Smena |
| payload | json | YES | | |
| created_at / updated_at | timestamp | YES | | |

### 7.5 yandex_smena_shifts

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| shiftable_type | string | NO | | `OrderActivities` / `TaskActivity` |
| shiftable_id | bigint unsigned | NO | | Локальный ID активности |
| external_id | string | YES | | ID смены в Yandex.Smena |
| status | string | YES | | Локальный статус |
| payload | json | YES | | |
| created_at / updated_at | timestamp | YES | | |

### 7.6 yandex_smena_candidates

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| yandex_smena_shift_id | bigint unsigned | NO | | Ссылка на `yandex_smena_shifts` |
| external_id | string | YES | | ID кандидата в Yandex.Smena |
| status | string | YES | | Статус кандидата |
| payload | json | YES | | |
| created_at / updated_at | timestamp | YES | | |

### 7.7 yandex_smena_favorite_workers

| Колонка | Тип | Nullable | Описание |
|---------|-----|----------|----------|
| id | bigint unsigned | NO | PK, AI |
| external_id | string | NO | | ID избранного работника в Yandex.Smena |
| payload | json | YES | | |
| created_at / updated_at | timestamp | YES | | |

---

## 8. Схема связей (кратко)

- **users** ↔ **roles** через **user_roles**
- **users** ↔ **directory_project**, **directory_place**, **directory_counterparty** через **user_directory_*** 
- **users** ↔ **users** (manager–supervisor): **manager_supervisor** (user_id_manager, user_id_supervisor)
- **users** ↔ **users** (manager–specialist, supervisor–specialist): **manager_specialist**, **supervisor_specialist**
- **orders** → **users** (user_id, accept_user_id), **directory_place** (place_id)
- **orders** ↔ **order_activities** → **directory_view_activities**
- **orders** ↔ **accept_order** (user_id) — кто принял заказ
- **orders** → **tasks** (order_id), **bids** (order_id)
- **tasks** → **users** (user_id, accept_user_id, specialist_user_id), **directory_place**, **directory_project**
- **tasks** ↔ **task_activities**, **accept_task**
- **tasks** → **bids** (task_id), **search_request** (task_id)
- **bids** → **users** (user_id, accept_user_id), **orders**, **tasks**, **directory_place**, **directory_view_activities**
- **bids** ↔ **bid_activities**, **accept_bid** (user_id — специалист, accepted)
- **search_request** → **orders**, **tasks**, **users**, **directory_place**, **directory_view_activities**
- **requests** → **users** (user_id, accept_user_id), **orders**, **tasks**, **directory_place**, **directory_view_activities**
- **report** → **users**, **bids**, **orders**, **tasks**
- **report_reason** → **report**, **directory_reasons**
- **documents** → **users**
- **questionnaires** → **users**
- **yandex_smena_sites** → **directory_place**
- **yandex_smena_professions** → **directory_view_activities**
- **yandex_smena_shifts** → **OrderActivities** / **TaskActivity** (polymorphic `shiftable`)
- **yandex_smena_candidates** → **yandex_smena_shifts**

Все перечисленные таблицы создаются и изменяются миграциями в `database/migrations/`. Точный список колонок при расхождении следует сверять с конкретным файлом миграции.
