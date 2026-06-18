# Динамическая форма регистрации пользователя

Документ описывает механизм пошаговой регистрации пользователя через динамическую форму. Управляющий контроллер — `App\Http\Controllers\Form\FormController`.

---

## Общая архитектура

Форма регистрации строится динамически на основе записей в таблице `fields`. Каждое поле имеет:

- `uuid` — уникальный идентификатор поля;
- `step` — номер шага, на котором поле показывается;
- `type` — тип поля (текст, файл, фото, справочник и т.д.);
- `directory` — класс справочника, если тип «справочник»;
- `required` — обязательность заполнения;
- `parentFields` — JSON с условиями показа поля (зависимость от других полей);
- `preg_value` — регулярное выражение для валидации.

Данные пользователя накапливаются в поле `users.data` в виде JSON структуры:

```json
{
  "1": { "field-uuid-1": "value", "field-uuid-2": "value" },
  "2": { "field-uuid-3": "value" }
}
```

где ключ верхнего уровня — номер шага, а внутри — `uuid поля → значение`.

### Основные сервисы

| Сервис | Назначение |
|---|---|
| `FormBuilderService` | Строит форму для конкретного шага, фильтрует поля, проверяет обязательность и валидацию. |
| `CreatePdfFileService` | Конвертирует загруженные файлы (jpg, png, pdf, doc, docx) в единый PDF и сохраняет в хранилище. |
| `OneCServices` | Отправляет данные регистрации во внешнюю систему 1С. |
| `RecognitionDocumentService` | Создаёт записи для распознавания загруженных документов (паспорт, СНИЛС и т.д.). |
| `UserDataService` | Извлекает и нормализует пользовательские данные (ФИО, пол, адрес, паспортные данные). |
| `ApiTokenService` | Создаёт Passport-токен для доступа в личный кабинет после завершения регистрации. |

### Форматирование полей для фронтенда

Каждое поле из таблицы `fields` перед отправкой клиенту преобразуется из ORM-модели в плоский JSON-объект, понятный мобильному/веб-приложению. За это отвечает набор классов в `app/Services/Formatter/Connectors/`.

#### Архитектура

- Интерфейс: `App\Services\Formatter\FormaterInterface`
  ```php
  public static function createFormat($fieldsData, $value): array;
  ```
- Базовая enum: `App\Enum\Fields\FieldsTypeEnum`
  - хранит числовые коды типов (`text = 7`, `file = 3`, `select = 6` и т.д.);
  - метод `typeClassFormatter()` сопоставляет каждый тип с классом-форматером.

#### Пример маппинга типов

| Тип поля (БД) | Класс форматера | Что делает |
|---|---|---|
| `text` | `TextFormatter` | Генерирует текстовый input с placeholder, required-валидацией, regex. |
| `file` | `FileFormatter` | Генерирует поле загрузки файла, добавляет URL `api/saveFile/`. |
| `select` | `SelectFormatter` | Генерирует выпадающий список из `valuesDirectory`. |
| `checkbox` | `CheckBoxFormatter` | Генерирует чекбокс. |
| `photo` | `PhotoFormatter` | Генерирует поле загрузки фото. |
| `date` | `DateFormatter` | Форматирует поле даты. |
| `phone` | `PhoneFormatter` | Форматирует телефон. |
| `inn`, `snils`, `bic`, `account`, `card` | `InnFormatter`, `SnilsFormatter`, `BicFormatter`, `AccountFormatter`, `CardFormatter` | Специализированные маски/валидации. |
| `autocomplete` | `AutocompleteFormatter` | Селект с поиском. |

#### Как это работает в `FormBuilderService`

В методе `formatData()` для каждого поля текущего шага вызывается:

```php
$fieldDataFormat = FieldsTypeEnum::from($field->type)
    ->typeClassFormatter()::createFormat($field, $value);
```

То есть:
1. По числовому `type` поля получаем enum-case.
2. Через `typeClassFormatter()` получаем имя класса-форматера.
3. Вызываем статический `createFormat()`, передавая объект поля и текущее значение из `users.data`.

#### Структура выходного JSON одного поля

```json
{
  "inputType": "text",
  "name": "field-uuid",
  "value": "текущее значение",
  "disabled": false,
  "validation": "default",
  "placeholder": "Введите значение",
  "heading": "Заголовок поля",
  "helperInfo": {
    "text": "Подсказка",
    "link": { "path": "...", "text": "...", "type": "..." }
  },
  "pregValue": "base64(regex)",
  "pregText": "Описание валидации"
}
```

#### Особенности

- Если поле имеет `updateData` (значение на модерации), форматер отмечает его `status: "warning"`, `disabled: true` и выводит подсказку «Значение поля находится на модерации».
- Для справочников (`directory`) `FormBuilderService` предварительно подгружает `valuesDirectory` и `default`, а затем использует соответствующий форматер (`select`, `radio`, `checkbox` и т.д.).
- Для полей типа `file` в JSON добавляется URL эндпоинта загрузки (`/api/saveFile/`), чтобы фронтенд знал, куда отправлять файлы.

### Маршруты API

```php
Route::get('/getForm/', [FormController::class, 'getForm'])->name('getForm');
Route::post('/saveForm/', [FormController::class, 'saveForm'])->name('saveForm');
Route::post('/saveFile/', [FormController::class, 'saveFile'])->name('saveFile');
Route::post('/finishRegister/', [FormController::class, 'finishRegister'])->name('finishRegister');
```

---

## `getForm(Request $request)`

**Метод:** `GET /api/getForm/?step={number}`

**Назначение:** возвращает структуру полей для указанного шага регистрации, а также текущий статус заполненности шага.

### Алгоритм работы

1. Получает текущего авторизованного пользователя (`Auth::user()`).
2. Если `finishRegister = true`, сразу возвращает `status: error` — регистрация уже завершена.
3. Определяет номер шага:
   - из query-параметра `step`;
   - если не передан — использует шаг `1`.
4. Загружает ранее сохранённые данные пользователя из `user->data` (JSON → массив).
5. Создаёт `FormBuilderService($step, $userData)`.
6. Вызывает `createFormData()` — формирует массив полей для текущего шага с учётом:
   - активных полей (`active = true`);
   - полей, относящихся к текущему шагу;
   - фильтрации по `parentFields` (условия видимости);
   - подгрузки справочников;
   - подстановки уже введённых значений;
   - преобразования каждого поля в JSON через соответствующий `Formatter` (`app/Services/Formatter/Connectors/`).
7. Вызывает `checkStatusForm(true)` — определяет статус:
   - `needRequired` — не все обязательные поля заполнены;
   - `allowedNewStep` — шаг можно считать завершённым;
   - `addedNewFields` — добавлены новые поля, требуется дозаполнение;
   - `pregNotValid` — значения не проходят regex-валидацию.
8. Формирует ответ:

```json
{
  "status": "success",
  "result": {
    "formData": [ /* массив полей */ ],
    "step": 1,
    "type": "needRequired|allowedNewStep|addedNewFields|pregNotValid"
  }
}
```

### Важные детали

- Пользователь должен быть авторизован (обычно через токен со scope `register`).
- Метод только читает данные, ничего не сохраняет.
- Поля со справочниками получают дополнительные атрибуты `valuesDirectory` и `default`.

---

## `saveForm(Request $request)`

**Метод:** `POST /api/saveForm/`

**Тело запроса:**

```json
{
  "step": 1,
  "formData": {
    "field-uuid-1": "value",
    "field-uuid-2": "value"
  }
}
```

**Назначение:** сохраняет данные конкретного шага регистрации в `users.data` и возвращает статус заполненности шага.

### Алгоритм работы

1. Получает текущего пользователя.
2. Если `finishRegister = true`, возвращает `status: error`.
3. Проверяет наличие `step` в запросе. Если отсутствует — возвращает ошибку:
   ```json
   { "status": "error", "error": "Поле step обязательна для заполнения" }
   ```
4. Если передан `formData`:
   - Загружает существующие данные пользователя из `user->data`.
   - Записывает/перезаписывает данные текущего шага:
     ```php
     $userData[$step] = $request->formData;
     $user->data = json_encode($userData);
     $user->save();
     ```
5. Снова загружает актуальные `formData` (уже после сохранения).
6. Создаёт `FormBuilderService($step, $formData)` и вызывает `getStepField()` — загружает поля текущего шага.
7. Вызывает `checkStatusForm()` (без флага `getForm`) и возвращает результат:

```json
{
  "status": "success",
  "result": {
    "step": 1,
    "type": "needRequired|allowedNewStep|addedNewFields|pregNotValid"
  }
}
```

### Важные детали

- `formData` может быть пустым — тогда метод просто проверит статус шага на основе уже сохранённых данных.
- Сохранение происходит шаг за шагом; старые данные других шагов не затираются.
- Валидация выполняется только на уровне статуса формы (`checkStatusForm`); отдельных HTTP-422 здесь нет.

---

## `saveFile(Request $request)`

**Метод:** `POST /api/saveFile/`

**Тело запроса:** `multipart/form-data`

| Поле | Описание |
|---|---|
| `file[]` | Файл(ы) в формате jpg, png, pdf, doc, docx (до 6 MB) |
| `fieldUuid` | UUID поля формы, в которое загружается файл |

**Назначение:** загружает файлы, конвертирует их в единый PDF и возвращает публичную ссылку на результат.

### Алгоритм работы

1. Получает текущего пользователя.
2. Проверяет наличие `fieldUuid`. Если отсутствует — возвращает `status: error`.
3. Получает все загруженные файлы из запроса (`$request->allFiles()`).
4. Нормализует массив файлов:
   - если пришёл один файл — оборачивает в массив;
   - если массив — использует как есть.
5. Создаёт `CreatePdfFileService($files, $userId, $user->phone, $fieldUuid)`.
6. Сервис выполняет следующее:
   - Находит поле по `fieldUuid` в таблице `fields`.
   - Конвертирует каждый файл в PDF:
     - `pdf` — остаётся как есть;
     - `doc`/`docx` — через PhpWord + mPDF;
     - `jpg`/`jpeg`/`png` — через Dompdf (HTML с изображением).
   - Объединяет все PDF в один через FPDI.
   - Сохраняет результат в `storage/app/public/source/pdf/{userId}/{random}/[{phone}][{fieldName}].pdf`.
7. Возвращает результат:
   - успех: `{ "status": "success", "resFile": "https://.../storage/source/pdf/..." }`;
   - ошибка: `{ "status": "error", "error": "текст ошибки" }`.

### Важные детали

- Несколько файлов всегда сливаются в один PDF.
- Имя итогового файла содержит телефон пользователя и название поля.
- Оригинальные расширения поддерживаются: pdf, doc, docx, jpg, jpeg, png.
- Если конвертация или merge падает, пользователь получает сообщение с предложением заменить файл на скриншоты.

---

## `finishRegister(Request $request)`

**Метод:** `POST /api/finishRegister/`

**Назначение:** финализирует регистрацию пользователя: отправляет данные в 1С, запускает распознавание документов, нормализует пользовательские данные и выдаёт токен для входа в личный кабинет.

### Алгоритм работы

1. Получает текущего пользователя.
2. Если `finishRegister = true`, возвращает `status: error` — регистрация уже завершена.
3. Отправляет данные регистрации в 1С:
   ```php
   $registerResult = (new OneCServices($user))->sendRegister();
   ```
   - `OneCServices` через `OneCServicesClient` делает HTTP-запрос в 1С.
   - В ответе ожидается `status` и `uuid` пользователя во внешней системе.
4. Если `sendRegister()` вернул `status = false`:
   - возвращает `status: error`;
   - регистрация не завершается.
5. Если регистрация в 1С успешна:
   - Устанавливает `user->finishRegister = true`.
   - Сохраняет `uuid` из 1С в `user->uuid`.
   - Преобразует `user->data`:
     ```php
     $user->data = json_encode(array_merge(...json_decode($user->data, true)));
     ```
     То есть все шаги объединяются в один плоский массив `uuid поля → значение`.
6. Создаёт документы на распознавание:
   ```php
   (new RecognitionDocumentService($dataForDoc, $user))->createDocumentForRecognition();
   ```
   - Для каждого поля из `DocumentFieldTypeEnum::options()`, значение которого не пустое, создаётся запись в `recognition_documents`.
   - Статус записи — `pending`.
7. Проверяет и дополняет пользовательские данные:
   ```php
   UserDataService::userFieldsCheckRegister($dataForDoc, $user);
   ```
   - На текущий момент это поиск местоположения (`searchPlace`) по полю `vk0wcCKTq7sP67Iybq19sLyJckCZzz`.
   - Если найдены координаты — сохраняет `latitude` и `longitude` в пользователя.
8. Сохраняет пользователя.
9. Создаёт токен доступа:
   ```php
   $apiTokenService = new ApiTokenService($user);
   $token = $apiTokenService->createToken(['personalArea']);
   ```
   - Удаляет все предыдущие токены пользователя.
   - Создаёт Passport personal access token со scope `personalArea`.
10. Возвращает ответ:

```json
{
  "status": "success",
  "result": {
    "token": {
      "token_type": "Bearer",
      "expires_in": 1296000,
      "access_token": "...",
      "refresh_token": "..."
    }
  }
}
```

### Важные детали

- Регистрация необратима: после успешного выполнения `finishRegister = true` и повторные вызовы методов формы будут возвращать ошибку.
- Отправка в 1С — критичный шаг; если он падает, весь процесс откатывается.
- Распознавание документов запускается асинхронно через создание записей `RecognitionDocument`; сами файлы уже должны быть загружены через `saveFile`.
- Scope токена `personalArea` отличается от токена регистрации `register`.

---

## Поток данных при регистрации

```
1. Пользователь вводит телефон → RegistrationController::sendPhone
2. Подтверждает SMS-код → RegistrationController::checkCode
   └── получает токен со scope 'register'
3. GET /api/getForm/?step=1 → FormController::getForm
   └── видит поля 1-го шага
4. POST /api/saveForm/ → FormController::saveForm
   └── сохраняет данные 1-го шага
5. POST /api/saveFile/ → FormController::saveFile
   └── загружает документы, получает URL PDF
6. Повторяет шаги 3-5 для шагов 2, 3, ...
7. POST /api/finishRegister/ → FormController::finishRegister
   └── 1С → распознавание → токен 'personalArea'
```

---

## Связь с другими сущностями

| Таблица / Класс | Роль в регистрации |
|---|---|
| `users` | Хранит `data`, `finishRegister`, `uuid`, `img`, `phone`, `email`. |
| `fields` | Описывает поля формы: тип, шаг, обязательность, справочники, условия. |
| `recognition_documents` | Задачи на распознавание загруженных документов. |
| `offer_search` / `counterparties` / `organizations` | Справочники мест и организаций. |
| 1С (внешняя система) | Приём финальных данных регистрации. |
