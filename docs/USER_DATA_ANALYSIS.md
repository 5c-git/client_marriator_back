# Анализ данных пользователя

Этот документ описывает, как хранятся пользовательские данные формы регистрации, и даёт готовые инструменты для анализа конкретного пользователя в будущих сессиях.

---

## Где хранятся данные

Все данные формы регистрации сохраняются в поле `users.data` в формате JSON.

### Два формата хранения

#### 1. Во время регистрации (до `finishRegister`)

Данные сгруппированы по шагам:

```json
{
  "1": {
    "field-uuid-1": "value",
    "field-uuid-2": "value"
  },
  "2": {
    "field-uuid-3": "value"
  },
  "3": {
    "field-uuid-4": "value"
  }
}
```

- Ключ верхнего уровня — номер шага (`step` из таблицы `fields`).
- Внутри — `uuid поля → значение`.

#### 2. После завершения регистрации (`finishRegister = 1`)

Метод `FormController::finishRegister()` схлопывает все шаги в один плоский массив:

```php
$user->data = json_encode(array_merge(...json_decode($user->data, true)));
```

Получается:

```json
{
  "field-uuid-1": "value",
  "field-uuid-2": "value",
  "field-uuid-3": "value"
}
```

- Ключи — UUID-ы полей из таблицы `fields`.
- Значения могут быть строками, массивами, `null`, URL-ами файлов.

---

## Структура таблицы `fields`

| Колонка | Назначение |
|---|---|
| `uuid` | Уникальный идентификатор поля. Используется как ключ в `users.data`. |
| `name` | Человекочитаемое название поля. |
| `type` | Числовой тип поля (см. `App\Enum\Fields\FieldsTypeEnum`). |
| `step` | Номер шага регистрации, на котором показывается поле. |
| `directory` | Класс модели справочника (`App\Models\Fields\Directory\*`), если поле типа «Справочник». |
| `required` | Обязательно ли поле. |
| `parentFields` | JSON с условиями видимости поля. |
| `preg_value` | Регулярное выражение для валидации. |

### Основные типы полей

| Код | Название | Где значение |
|---|---|---|
| 1 | Чекбокс | `0`/`1` или пустая строка |
| 2 | Множественный чекбокс | массив UUID-ов |
| 3 | Файл | URL на PDF |
| 4 | Чекбокс с фото | массив UUID-ов |
| 5 | Радио | UUID значения |
| 6 | Список | UUID значения |
| 7 | Текст | произвольная строка |
| 8 | Справочник | UUID значения или массив UUID-ов |
| 9 | Лицевой счёт | строка цифр |
| 10 | Банковская карта | строка цифр |
| 11 | Дата | дата в формате `Y-m-d` |
| 12 | Email | email-строка |
| 13 | ИНН | строка цифр |
| 14 | Дата до месяца | дата в формате `Y-m-d` |
| 15 | Телефон | строка цифр |
| 16 | SMS | строка цифр |
| 17 | СНИЛС | строка цифр |
| 18 | Фото | URL на изображение |
| 19 | Селект с поиском | UUID значения |
| 20 | Множественный селект | массив UUID-ов |
| 21 | BIC | UUID значения из справочника банков |

---

## Справочники

Справочники — это отдельные таблицы `directory_*`. Каждая запись имеет:

- `uuid` — UUID значения (хранится в `users.data`);
- `name` — человекочитаемое название;
- `active` — активно ли значение;
- `parentFields` — условия видимости (JSON).

### Модели справочников

Находятся в `app/Models/Fields/Directory/` и реализуют `ModelDirectoryInterface`:

```php
public static function getAllData(): Collection;
public function getDataDirectory(bool $allFields = false, array $filterData = []);
public static function getDefault(): string|array;
```

Примеры справочников:

| Модель | Таблица | Что содержит |
|---|---|---|
| `Activities` | `directory_activities` | Направления деятельности |
| `ViewActivities` | `directory_view_activities` | Виды деятельности |
| `TaxStatus` | `directory_tax_status` | Налоговые статусы |
| `Citizenship` | `directory_citizenship` | Гражданства |
| `OfferSearch` | `directory_offer_search` | Территории поиска |
| `Gender` | `directory_gender` | Пол |
| `Age` | `directory_age` | Возраст |
| `Height` | `directory_height` | Рост |
| `Weight` | `directory_weight` | Вес |
| `ClothingSize` | `directory_clothing_size` | Размер одежды |
| `ShoeSize` | `directory_shoe_size` | Размер обуви |
| `HairColor` | `directory_hair_color` | Цвет волос |
| `HairLength` | `directory_hair_length` | Длина волос |
| `Bank` | `directory_bank` | Банки |
| `MedicalBook` | `directory_medical_book` | Статусы медкнижки |
| `Documentation` | `directory_documentation` | Виды документов |
| `Messengers` | `directory_messengers` | Мессенджеры |

---

## Быстрые SQL-запросы

### Найти пользователей с самыми заполненными данными

```sql
SELECT id, name, phone, finishRegister, confirmRegister, LENGTH(data) AS data_len
FROM users
WHERE data IS NOT NULL AND LENGTH(data) > 10
ORDER BY data_len DESC
LIMIT 10;
```

### Посмотреть сырые данные конкретного пользователя

```sql
SELECT id, data FROM users WHERE id = 184;
```

### Посмотреть все активные поля формы

```sql
SELECT id, uuid, name, type, step, directory, required
FROM fields
WHERE active = 1
ORDER BY step, sort;
```

### Посмотреть содержимое справочника

```sql
SELECT uuid, name, active FROM directory_tax_status WHERE active = 1;
SELECT uuid, name, active FROM directory_activities WHERE active = 1;
SELECT uuid, name, active FROM directory_gender WHERE active = 1;
```

---

## Готовый скрипт для разбора данных пользователя

Создай временный файл в корне проекта (например, `analyze_user.php`), замени `USER_ID` на нужный, запусти через Tinker.

```php
<?php

use App\Models\User;
use App\Models\Fields\Fields;
use App\Enum\Fields\FieldsTypeEnum;

$USER_ID = 184; // <-- заменить на нужный ID

$user = User::find($USER_ID);
if (!$user) {
    echo "User not found\n";
    return;
}

$data = $user->data;
if (is_string($data)) {
    $data = json_decode($data, true);
}

// Если данные ещё в формате "по шагам" — схлопываем в плоский массив
$flatData = [];
if (is_array($data) && array_is_list($data) === false) {
    foreach ($data as $step => $stepData) {
        if (is_array($stepData)) {
            $flatData = array_merge($flatData, $stepData);
        } else {
            // Уже плоский формат
            $flatData[$step] = $stepData;
        }
    }
}

$fields = Fields::where('active', true)->get()->keyBy('uuid');

$lines = [];
$lines[] = "User ID: {$user->id}";
$lines[] = "Phone: {$user->phone}";
$lines[] = "finishRegister: {$user->finishRegister}";
$lines[] = "confirmRegister: {$user->confirmRegister}";
$lines[] = "Total fields in data: " . count($flatData);
$lines[] = str_repeat('=', 80);

foreach ($flatData as $fieldUuid => $value) {
    $field = $fields->get($fieldUuid);

    if (!$field) {
        $lines[] = "[$fieldUuid] => UNKNOWN FIELD (not in fields table)";
        $lines[] = "  Raw value: " . json_encode($value, JSON_UNESCAPED_UNICODE);
        $lines[] = "";
        continue;
    }

    $typeName = FieldsTypeEnum::tryFrom($field->type)?->typeName() ?? "type({$field->type})";
    $lines[] = "[{$field->uuid}] {$field->name}";
    $lines[] = "  Type: {$typeName} ({$field->type}) | Step: {$field->step} | Required: " . ($field->required ? 'yes' : 'no');

    // Разрешение справочников
    if (!empty($field->directory) && class_exists($field->directory)) {
        $directoryClass = $field->directory;
        $directoryItems = $directoryClass::getAllData()->keyBy('uuid');

        if (is_array($value)) {
            $lines[] = "  Value (directory, multiple):";
            foreach ($value as $v) {
                $item = $directoryItems->get($v);
                $lines[] = "    - " . ($item ? "{$v} => {$item->name}" : "{$v} => NOT FOUND");
            }
        } else {
            $item = $directoryItems->get($value);
            $lines[] = "  Value (directory): " . ($item ? "{$value} => {$item->name}" : "{$value} => NOT FOUND");
        }
    } else {
        if (is_array($value)) {
            $lines[] = "  Value (array): " . json_encode($value, JSON_UNESCAPED_UNICODE);
        } elseif (is_null($value)) {
            $lines[] = "  Value: NULL";
        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
            $lines[] = "  Value (URL/file): {$value}";
        } else {
            $lines[] = "  Value: {$value}";
        }
    }

    $lines[] = "";
}

$outputPath = '/var/www/html/storage/app/user_analysis_' . $USER_ID . '.txt';
file_put_contents($outputPath, implode("\n", $lines));
echo "Done. See: {$outputPath}\n";
```

### Как запустить

```bash
# 1. Создать файл analyze_user.php в корне проекта с кодом выше
# 2. Запустить через Tinker
./vendor/bin/sail artisan tinker --execute='include("/var/www/html/analyze_user.php");'

# 3. Прочитать результат
cat storage/app/user_analysis_184.txt
```

---

## Пример результата (пользователь #184)

```
User ID: 184
Phone: 79269453055
finishRegister: 1
confirmRegister: 1
Total fields in data: 45
================================================================================
[gov] Гражданство
  Type: Справочник (8) | Step: 2 | Required: yes
  Value (directory): 982d33e2-bee6-453a-992e-11d13fa66fa7 => РОССИЯ

[testitem] Направление деятельности
  Type: Справочник (8) | Step: 1 | Required: yes
  Value (directory, multiple):
    - c0e077b8-d11d-11eb-85fa-6cb3110f7042 => Безопасность
    - 5585f071-e45e-11e6-86d3-10bf48d7f390 => Рекрутинг
    - ...

[vidideayt] Виды деятельности
  Type: Справочник (8) | Step: 2 | Required: yes
  Value (directory, multiple):
    - directory_view_activities_AudJFcNa8lg8QJVLmHQjGqXxe4T1ZY => Пекарь
    - directory_view_activities_DJZ8mtC9ZcV4H1Fb1w3gGiU9sBLH8u => Продавец
    - directory_view_activities_e7JEmQrGSvQ2JCDJlgTd09jNoiFfGm => Курьер

[nalogstatus] Налоговый статус
  Type: Справочник (8) | Step: 2 | Required: yes
  Value (directory): nalogstatus_fiz_lico => Физическое лицо

[staticEmail] => UNKNOWN FIELD (not in fields table)
  Raw value: "hadimaek.07@gmail.com"

[staticPhoto] => UNKNOWN FIELD (not in fields table)
  Raw value: "http://preprod.marriator-api.fivecorners.ru/storage/source/userImg/184/..."
```

---

## Частые сценарии анализа

### 1. Проверить, заполнил ли пользователь конкретное поле

```php
$user = User::find(184);
$data = is_string($user->data) ? json_decode($user->data, true) : $user->data;
$flatData = [];
foreach ($data as $step => $stepData) {
    if (is_array($stepData)) {
        $flatData = array_merge($flatData, $stepData);
    } else {
        $flatData[$step] = $stepData;
    }
}

$fieldUuid = 'gov'; // uuid поля
$value = $flatData[$fieldUuid] ?? null;
```

### 2. Получить человекочитаемое значение справочника

```php
$field = Fields::where('uuid', 'gov')->first();
$directoryClass = $field->directory;
$item = $directoryClass::where('uuid', $value)->first();
$name = $item?->name ?? 'NOT FOUND';
```

### 3. Найти всех пользователей, у которых заполнено поле

```sql
SELECT id, phone
FROM users
WHERE JSON_CONTAINS_PATH(data, 'one', '$."gov"')
   OR JSON_CONTAINS_PATH(data, 'one', '$.*."gov"');
```

> Первый вариант — плоский формат, второй — формат по шагам.

### 4. Найти пользователей с определённым значением поля

```sql
-- Плоский формат
SELECT id, phone FROM users WHERE data->>'$."gov"' = '982d33e2-bee6-453a-992e-11d13fa66fa7';

-- Формат по шагам (ищем во всех шагах)
SELECT id, phone FROM users WHERE data LIKE '%"982d33e2-bee6-453a-992e-11d13fa66fa7"%';
```

---

## Важные нюансы

1. **После `finishRegister` данные плоские.** Если ты видишь в `users.data` ключи-числа (`"1"`, `"2"`), значит регистрация ещё не завершена.
2. **UUID поля ≠ UUID значения справочника.** В `users.data` хранятся UUID-ы полей из `fields`, а для справочников — UUID-ы значений из `directory_*`.
3. **Не все поля есть в `fields`.** Например, `staticEmail` и `staticPhoto` — захардкоженные поля, они не описаны в таблице полей.
4. **Файлы хранятся как URL.** Сами файлы лежат в `storage/app/public/source/pdf/{userId}/{random}/`, а в базе — публичный URL.
5. **Для анализа лучше использовать Laravel/Tinker**, а не чистый SQL, потому что нужно разрешать справочники и обрабатывать разные форматы данных.

---

## Ссылки

- `docs/FORM_REGISTRATION.md` — общий поток регистрации.
- `app/Services/FormBuilderService.php` — построение формы.
- `app/Services/Formatter/Connectors/` — форматирование полей для фронтенда.
- `app/Models/Fields/Directory/` — модели справочников.
