# Документация проекта Marriator

Подробное описание логики, маршрутов, миграций, моделей, контроллеров и сервисов.

**Детальная документация (отдельные файлы):**

- **[DATABASE.md](DATABASE.md)** — структура БД: все таблицы, колонки, типы, связи.
- **[CONTROLLERS.md](CONTROLLERS.md)** — логика контроллеров: что делает каждый метод, вызываемый из роутинга.
- **[SERVICES.md](SERVICES.md)** — как работают сервисы: методы, зависимости, потоки данных.

---

## 1. Обзор проекта

**Marriator** — Laravel-приложение для управления заказами, задачами, ставками специалистов и отчётами. Включает:

- **Админ-панель** (web) — управление пользователями, полями форм, справочниками, настройками, документами, тестовыми сущностями.
- **REST API** — регистрация/авторизация по телефону и PIN, личный кабинет, роли (client, manager, recruiter, supervisor, specialist), полный цикл: заказ → задача → поиск → ставка → работа → отчёт → оплата.
- **Интеграции**: Laravel Passport (OAuth2), Nopaper (ЭДО), 1C, PVP (XFive, Verme, TimeBook), распознавание документов (Correct).

**Роли пользователей:** `admin`, `client`, `manager`, `recruiter`, `supervisor`, `specialist`.

---

## 2. Структура проекта

| Директория | Назначение |
|------------|------------|
| `app/` | Ядро: модели, контроллеры, сервисы, команды, провайдеры, enum, middleware, observers |
| `app/Console/Commands/` | Artisan-команды: архивация, отчёты, PVP, распознавание документов |
| `app/Http/Controllers/` | Admin (панель), Form, PersonalArea, UserRoles, UniversalController, Integration, Settings |
| `app/Http/Middleware/` | CheckPermission, CheckRole, CheckIntegration |
| `app/Models/` | User, Order/Bid/Task/Report/SearchRequest/Request, Document, Fields, справочники, Setting, Certificates |
| `app/Services/` | Регистрация/SMS/Email, API-токены, PVP, документы (Nopaper, распознавание, PDF), репозитории Order/User, форматтеры полей |
| `config/` | app, auth, database, passport, queue, services, adminlte, l5-swagger и др. |
| `database/migrations/` | Миграции БД (users, orders, tasks, bids, reports, справочники, OAuth, документы, настройки) |
| `routes/` | web.php (админка + Passport), api.php (REST API), console.php (расписание команд) |
| `resources/views/` | Blade-шаблоны админки |

---

## 3. Маршруты (Routes)

### 3.1 Файлы маршрутов

- **routes/web.php** — веб-интерфейс админки и OAuth Passport.
- **routes/api.php** — REST API (все маршруты с префиксом `/api`).
- **routes/console.php** — только расписание Artisan-команд (HTTP-маршрутов нет).

---

### 3.2 routes/web.php

#### Passport (OAuth)

| Метод | URI | Имя | Контроллер/действие |
|-------|-----|-----|----------------------|
| POST | `/oauth/token` | passport.token | Laravel Passport `AccessTokenController@issueToken` (throttle) |

Префикс задаётся в `config('passport.path', 'oauth')`.

#### Админка — без авторизации

| Метод | URI | Имя | Описание |
|-------|-----|-----|----------|
| GET | `/admin/login/` | adminLogin | Страница входа (view admin.login) |
| POST | `/admin/loginAdminAjax/` | loginAdminAjax | Admin\Auth\LoginController@customAdminLogin |

#### Админка — с middleware CheckPermission (доступ только для админа)

| Метод | URI | Имя | Контроллер/действие |
|-------|-----|-----|----------------------|
| GET | `/admin` | mainPage | Admin\Page\MainPageController@mainPage |
| GET/POST | `/admin/logout` | logout | Admin\Auth\LoginController@logout |

**Пользователи (admin/users):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/users/create/ | usersCreate | Форма создания |
| POST | /admin/users/createAjax/ | usersCreateAjax | Создание |
| GET | /admin/users/ | usersList | Список |
| GET | /admin/users/edit/{id}/ | userEdit | Форма редактирования |
| POST | /admin/users/editAjax/ | userEditAjax | Сохранение |
| GET | /admin/users/delete/{id}/ | userDelete | Удаление |

**Поля форм (admin/fields):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/fields/create/ | fieldsCreate | Форма создания |
| POST | /admin/fields/createAjax/ | fieldsCreateAjax | Создание |
| GET | /admin/fields/ | fieldsList | Список |
| GET | /admin/fields/edit/{id}/ | fieldsEdit | Редактирование |
| POST | /admin/fields/editAjax/ | fieldsEditAjax | Сохранение |
| GET | /admin/fields/delete/{id}/ | fieldsDelete | Удаление |

**Справочники (admin/directories/):**  
Для каждого справочника — один и тот же набор: create, createAjax, list, edit/{id}, editAjax, delete.

- directory_country — CountryController  
- directory_bank — BankController  
- directory_activities — ActivitiesController  
- directory_tax_status — TaxStatusController  
- directory_citizenship — CitizenshipController  
- directory_residence — ResidenceController  
- directory_region_of_residence — RegionOfResidenceController  
- directory_offer_search — OfferSearchController  
- directory_view_activities — ViewActivitiesController  
- directory_weight — WeightController  
- directory_height — HeightController  
- directory_shoe_size — ShoeSizeController  
- directory_clothing_size — ClothingSizeController  
- directory_hair_color — HairColorController  
- directory_hair_length — HairLengthController  
- directory_gender — GenderController  
- directory_messengers — MessengersController  
- directory_documentation — DocumentationController  
- directory_organization — OrganizationController  
- directory_age — AgeController  
- directory_medical_book — MedicalBookController  
- directory_project — ProjectController  
- directory_brand — BrandController  
- directory_counterparty — CounterpartyController  
- directory_place — PlaceController  
- directory_standard — StandardController  
- directory_radius — RadiusController  
- directory_reasons — ReasonsController  

**Импорт справочников (admin/importDirectory):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/importDirectory/ | index | Страница импорта |
| POST | /admin/importDirectory/import | import | Импорт |
| POST | /admin/importDirectory/importSave | importSave | Сохранение импорта |

**Настройки (admin/settings):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/settings/ | settingIndex | Страница настроек |
| POST | /admin/settings/saveAjax | settingSave | Сохранение |

**Сертификаты (admin/certificates):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/certificates/ | certificatesIndex | Страница |
| POST | /admin/certificates/saveAjax | certificatesSave | Сохранение |

**QR-код (admin/qr_code):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/qr_code/ | qrCodeIndex | Страница |
| POST | /admin/qr_code/getBindings | getBindings | Получение привязок |
| POST | /admin/qr_code/createUserLink | createUserLink | Создание ссылки пользователя |

**Тестовые сущности:**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /admin/orderForTest/ | orderTestList | Admin\ForTest\OrderController@list |
| GET | /admin/orderForTest/delete/{id}/ | orderTestDelete | delete |
| GET | /admin/taskForTest/ | taskTestList | Admin\ForTest\TaskController@list |
| GET | /admin/taskForTest/delete/{id}/ | taskTestDelete | delete |
| GET | /admin/bidForTest/ | bidTestList | Admin\ForTest\BidController@list |
| GET | /admin/bidForTest/delete/{id}/ | bidTestDelete | delete |

**Документы (admin/documents):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /admin/documents/test | documentTest | Тест документа |
| POST | /admin/documents/save | documentTestSave | Сохранение теста |
| POST | /admin/documents/download | checkDocument | Проверка/скачивание |
| GET | /admin/documents/create/{templateType} | documentsCreate | Форма создания |
| POST | /admin/documents/createAjax/ | documentsCreateAjax | Создание |
| GET | /admin/documents/{templateType} | documentsList | Список по типу |
| GET | /admin/documents/edit/{id}/ | documentsEdit | Редактирование |
| POST | /admin/documents/editAjax/ | documentsEditAjax | Сохранение |
| GET | /admin/documents/delete/{id} | documentsDelete | Удаление |

---

### 3.3 routes/api.php

Все маршруты ниже имеют префикс **/api** (настраивается в bootstrap/app или RouteServiceProvider).

#### Публичные (без auth)

| Метод | URI | Имя | Контроллер/действие |
|-------|-----|-----|----------------------|
| POST | /api/sendPhone/ | sendPhone | Form\RegistrationController@sendPhone |
| GET | /api/getUserByHash/ | getUserByHash | Form\RegistrationController@getUserByHash (throttle 1000/1 мин) |
| GET | /api/login | login | Closure — возврат 401 JSON |
| POST | /api/checkCode/ | checkCode | Form\RegistrationController@checkCode |
| POST | /api/refreshToken/ | refreshToken | PersonalArea\CheckPinController@refreshToken |

#### auth:api (любой авторизованный)

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /api/personal/getData | getData | UniversalController@getData — данные текущего пользователя |

#### auth:api + scope:register (этап регистрации)

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /api/getUserInfo/ | getUserInfoInReg | Form\FormController@getUserInfo |
| GET | /api/getForm/ | getForm | Form\FormController@getForm |
| POST | /api/saveForm/ | saveForm | Form\FormController@saveForm |
| POST | /api/saveUserImg/ | saveUserImg | Form\FormController@saveUserImg |
| POST | /api/finishRegister/ | finishRegister | Form\FormController@finishRegister |
| POST | /api/setUserEmail/ | setUserEmail_reg | Form\RegistrationController@setUserEmail |
| POST | /api/checkEmailCode/ | checkEmailCode_reg | Form\RegistrationController@checkEmailCode |
| GET | /api/getBrand | getBrand | UniversalController@getBrand |
| POST | /api/setBrandImg | setBrandImg | UniversalController@setBrandImg |
| GET | /api/getPlace | getPlace | UniversalController@getPlace |
| POST | /api/setPlace | setPlace | UniversalController@setPlace |
| POST | /api/delPlace | delPlace | UniversalController@delPlace |
| POST | /api/setUserData | setUserData | UniversalController@setUserData |

#### auth:api + scope:register,personalArea

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| POST | /api/saveFile/ | saveFile | Form\FormController@saveFile |

#### auth:api + scope:register,restorePin,checkPin,personalArea

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| POST | /api/setUserPin/ | setUserPin | Form\RegistrationController@setUserPin |
| POST | /api/startRestorePin/ | startRestorePin | Form\RegistrationController@startRestorePin |
| POST | /api/checkCodeRestore/ | checkCodeRestore | Form\RegistrationController@checkCodeRestore |

#### auth:api + scope:checkPin

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| POST | /api/checkPin/ | checkPin | PersonalArea\CheckPinController@checkPin |

#### auth:api + scope:personalArea (личный кабинет)

**Личные данные и настройки:**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /api/personal/getUserInfo/ | getUserInfo | UserPersonalInfoController |
| GET | /api/personal/getUserFields/ | getUserFields | UserPersonalInfoController |
| GET | /api/personal/getUserPersonalMenu/ | getUserPersonalMenu | UserPersonalInfoController |
| POST | /api/personal/saveUserFields/ | saveUserFields | UserPersonalInfoController |
| POST | /api/personal/saveUserImg/ | saveUserImgPersonal | UserPersonalInfoController |
| POST | /api/personal/setUserEmail/ | setUserEmail | UserPersonalInfoController |
| POST | /api/personal/checkEmailCode/ | checkEmailCode | UserPersonalInfoController |
| POST | /api/personal/changeUserPhone/ | changeUserPhone | UserPersonalInfoController |
| POST | /api/personal/confirmChangeUserPhone/ | confirmChangeUserPhone | UserPersonalInfoController |
| GET | /api/personal/getRequisitesData/ | getRequisitesData | UserPersonalInfoController |
| GET | /api/personal/getEstateData/ | getEstateData | UserPersonalInfoController |
| POST | /api/personal/saveRequisitesData/ | saveRequisitesData | UserPersonalInfoController |
| POST | /api/personal/saveEstateData/ | saveEstateData | UserPersonalInfoController |
| POST | /api/personal/deleteRequisite/ | deleteRequisite | UserPersonalInfoController |
| POST | /api/personal/deleteEstate/ | deleteEstate | UserPersonalInfoController |
| GET | /api/personal/getFormActivities/ | getFormActivities | UserPersonalInfoController |
| POST | /api/personal/saveUserFieldsActivities/ | saveUserFieldsActivities | UserPersonalInfoController |
| GET | /api/personal/getBic/ | getBic | UserPersonalInfoController |
| GET | /api/personal/getMapField/ | getMapField | UserPersonalInfoController |
| POST | /api/personal/setMapField/ | setMapField | UserPersonalInfoController |

**Документы (personal/documents):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /api/personal/documents/getDocumentSigned/ | getDocumentSigned | DocumentsController |
| GET | /api/personal/documents/getDocumentArchive/ | getDocumentArchive | DocumentsController |
| GET | /api/personal/documents/getDocumentInquiries/ | getDocumentInquiries | DocumentsController |
| GET | /api/personal/documents/getDocumentConclude/ | getDocumentConclude | DocumentsController |
| GET | /api/personal/documents/getDocumentTerminate/ | getDocumentTerminate | DocumentsController |
| POST | /api/personal/documents/setConclude/ | setConclude | DocumentsController |
| POST | /api/personal/documents/setTerminate/ | setTerminate | DocumentsController |
| GET | /api/personal/documents/getCompanyAndCertificatesInquiries/ | getCompanyAndCertificatesInquiries | DocumentsController |
| POST | /api/personal/documents/requestInquiries/ | requestInquiries | DocumentsController |
| POST | /api/personal/documents/createTestDoc/ | createDocument | DocumentsController |

**Роль client (CheckRole:client):**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| POST | /api/personal/createOrder | createOrder | ClientController |
| POST | /api/personal/cancelOrder | cancelOrder | ClientController |
| POST | /api/personal/sendOrder | sendOrder | ClientController |
| POST | /api/personal/updateOrder | updateOrder | ClientController |
| POST | /api/personal/createOrderActivity | createOrderActivity | ClientController |
| POST | /api/personal/updateOrderActivity | updateOrderActivity | ClientController |
| POST | /api/personal/deleteOrderActivity | deleteOrderActivity | ClientController |
| GET | /api/personal/getViewActivitiesForOrder | getViewActivitiesForOrder | ClientController |
| POST | /api/personal/repeatOrder | repeatOrder | ClientController |

**Роль manager (CheckRole:manager):**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| POST | /api/personal/convertTask | convertTask | ManagerController |
| GET | /api/personal/getSurepvisorsForTask | getSurepvisorData | ManagerController |
| POST | /api/personal/createTask | createTask | ManagerController |
| POST | /api/personal/updateTask | updateTask | ManagerController |
| POST | /api/personal/createTaskActivity | createTaskActivity | ManagerController |
| POST | /api/personal/updateTaskActivity | updateTaskActivity | ManagerController |
| POST | /api/personal/deleteTaskActivity | deleteTaskActivity | ManagerController |
| GET | /api/personal/getViewActivitiesForTask | getViewActivitiesForTask | ManagerController |
| POST | /api/personal/instructTask | instructTask | ManagerController |
| POST | /api/personal/invoiceTask | invoiceTask | ManagerController |
| POST | /api/personal/cancelTask | cancelTask | ManagerController |
| GET | /api/personal/getProjectsForTask | getProjectForTask | ManagerController |
| POST | /api/personal/repeatTask | repeatTask | ManagerController |

**Роль recruiter (CheckRole:recruiter):**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /api/personal/request/getRequests | getRequests | RecruiterController |
| GET | /api/personal/request/getRequest | getRequest | RecruiterController |
| POST | /api/personal/request/acceptRequest | acceptRequest | RecruiterController |

**Роль specialist (CheckRole:specialist):**

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| POST | /api/personal/acceptBid | acceptBid | SpecialistController |
| POST | /api/personal/rejectBid | rejectBid | SpecialistController |
| POST | /api/personal/startDay | startDay | SpecialistController |
| POST | /api/personal/endDay | endDay | SpecialistController |
| POST | /api/personal/endJob | endJob | SpecialistController |
| POST | /api/personal/payReportSpecialist | payReport | SpecialistController |
| GET | /api/personal/getCounterpartiesForSign | getCounterpartiesForSign | SpecialistController |
| POST | /api/personal/signContracts | signContracts | SpecialistController |
| POST | /api/personal/signedDocument | signedDocuments | SpecialistController |
| POST | /api/personal/signedDocument/sendCode | signedDocumentsSendCode | SpecialistController |
| POST | /api/personal/signedDocument/retriesSms | signedDocumentsRetriesSms | SpecialistController |
| GET | /api/personal/signedDocument | getSignetDocument | SpecialistController |

**Общие для личного кабинета (UniversalController по роли):**

| Метод | URI | Имя | Описание |
|-------|-----|-----|----------|
| GET | /api/personal/getManager | getManager | Менеджеры |
| POST | /api/personal/setManagers | setManagers | Назначение менеджеров |
| POST | /api/personal/delManager | delManager | Удаление менеджера |
| GET | /api/personal/getOrders | getOrders | Список заказов |
| GET | /api/personal/getOrder | getOrder | Один заказ |
| POST | /api/personal/acceptOrder | acceptOrder | Принять заказ |
| GET | /api/personal/getTasks | getTasks | Список задач |
| GET | /api/personal/getTask | getTask | Одна задача |
| POST | /api/personal/createSearchFromOrder | createSearchFromOrder | Поиск из заказа |
| POST | /api/personal/createSearchFromTask | createSearchFromTask | Поиск из задачи |
| POST | /api/personal/updateSearch | updateSearch | Обновить поиск |
| GET | /api/personal/getBids | getBids | Список ставок |
| GET | /api/personal/getBid | getBid | Одна ставка |
| POST | /api/personal/invoiceBid | invoiceBid | Выставить счёт по ставке |
| POST | /api/personal/cancelBid | cancelBid | Отменить ставку |
| POST | /api/personal/acceptSpecialist | acceptSpecialist | Принять специалиста |
| POST | /api/personal/declinedSpecialist | declinedSpecialist | Отклонить специалиста |
| GET | /api/personal/getSpecialistForBid | getSpecialistForBid | Специалисты по ставке |
| POST | /api/personal/updateBid | updateBid | Обновить ставку |
| GET | /api/personal/getPlaceForOrder | getPlaceForOrderCreate | Места для заказа |
| GET | /api/personal/getPlaceForTask | getPlaceForTaskCreate | Места для задачи |
| GET | /api/personal/getPlaceForBid | getPlaceForBid | Места для ставки |
| GET | /api/personal/getRadiusSelect | getRadiusSelect | Радиусы |
| POST | /api/personal/acceptTask | acceptTask | Принять задачу |
| POST | /api/personal/createBidFromOrder | createBidFromOrder | Создать ставку из заказа |
| POST | /api/personal/createBidFromTask | createBidFromTask | Создать ставку из задачи |
| GET | /api/personal/getJobs | getJobs | Работы (принятые ставки) |
| GET | /api/personal/getJob | getJob | Одна работа |
| POST | /api/personal/endSpecialistJob | endJob | Завершить работу специалиста |
| POST | /api/personal/acceptReport | acceptReport | Принять отчёт |
| POST | /api/personal/acceptAllReportJob | acceptAllReportJob | Принять все отчёты по работе |
| POST | /api/personal/payReport | payReport | Оплатить отчёт |
| POST | /api/personal/updateReport | updateReport | Обновить отчёт |
| GET | /api/personal/getReasons | getReasons | Причины (для отчётов) |

**Модерация (personal/moderation):**

| Метод | URI | Имя | Действие |
|-------|-----|-----|----------|
| GET | /api/personal/moderation/getProject | getProject | Список проектов |
| POST | /api/personal/moderation/setProject | setProject | Создать/обновить проект |
| POST | /api/personal/moderation/delProject | delProject | Удалить проект |
| GET | /api/personal/moderation/getPlaceModeration | getPlaceModeration | Места модерации |
| POST | /api/personal/moderation/setPlaceModeration | setPlaceModeration | Сохранить место |
| POST | /api/personal/moderation/delPlaceModeration | delPlaceModeration | Удалить место |
| GET | /api/personal/moderation/getModerationClient | getModerationClient | Клиенты на модерации |
| GET | /api/personal/moderation/getModerationSingleClient | getModerationSingleClient | Один клиент |
| POST | /api/personal/moderation/confirmUserRegister | confirmUserRegister | Подтвердить регистрацию |
| POST | /api/personal/moderation/setUserImg | setUserImg | Загрузить фото пользователя |
| GET | /api/personal/moderation/getUserSurepvisorData | getUserSurepvisorData | Данные супервайзеров |
| GET | /api/personal/moderation/getSurepvisors | getSurepvisors | Супервайзеры |
| POST | /api/personal/moderation/setSurepvisors | setSurepvisors | Назначить супервайзеров |
| POST | /api/personal/moderation/delSurepvisor | delSurepvisor | Удалить супервайзера |
| GET | /api/personal/moderation/getCounterparty | getCounterparty | Контрагенты |
| POST | /api/personal/moderation/setCounterparty | setCounterparty | Сохранить контрагента |
| POST | /api/personal/moderation/deleteCounterparty | deleteCounterparty | Удалить контрагента |

#### Настройки приложения (auth:api)

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /api/settings/getFromKey/ | getFromKey | Settings\SettingsController@getFromKey |
| GET | /api/settings/getAll/ | getAll | Settings\SettingsController@getAll |

#### Интеграция (middleware CheckIntegration)

| Метод | URI | Имя | Контроллер |
|-------|-----|-----|------------|
| GET | /api/integration/ping/ | ping | Integration\IntegrationController@ping |
| POST | /api/integration/updateUserData/ | updateUserData | Integration\IntegrationController@updateUserData |

---

## 4. Миграции (database/migrations)

Миграции создают и изменяют таблицы. Ниже — основные таблицы и их назначение.

### 4.1 Пользователи и авторизация

- **users** — пользователи: id, name, email, password, remember_token, timestamps; далее добавлялись: phone, data (JSON), img, confirmRegister, pin, finishRegister, expansionData, errorData, estateData, requisitesData, mapAddress, mapRadius, updateData, change_fields, uuid, register_hash, флаги уведомлений, latitude, longitude, count_wait_bid, time_answer_bid, verme_id, nopaper_guid, nopaper_certificate_id, time_book_guid и др.
- **password_reset_tokens** — сброс пароля (email, token, created_at).
- **sessions** — сессии (id, user_id, ip_address, user_agent, payload, last_activity).
- **personal_access_tokens** — токены Sanctum (если используется).
- **oauth_auth_codes**, **oauth_access_tokens**, **oauth_refresh_tokens**, **oauth_clients**, **oauth_personal_access_clients** — Laravel Passport.

### 4.2 Роли

- **roles** — роли (id, name).
- **user_roles** — связь пользователь–роль (user_id, role_id).

### 4.3 Поля и справочники

- **fields** — поля форм: uuid, name, type, directory, step, sort, required, section, parentFields, estate, requisites, screen, role_id и др. (label, heading, placeholder, helperInfo, drawerInfo и т.д.).
- **fields_user_role** — связь полей с ролями (field_id, role_id).
- **directory_country**, **directory_bank**, **directory_activities**, **directory_tax_status**, **directory_citizenship**, **directory_residence**, **directory_region_of_residence**, **directory_offer_search**, **directory_view_activities**, **directory_weight**, **directory_height**, **directory_shoe_size**, **directory_clothing_size**, **directory_hair_color**, **directory_hair_length**, **directory_gender**, **directory_messengers**, **directory_documentation**, **directory_organization**, **directory_age**, **directory_medical_book**, **directory_project**, **directory_brand**, **directory_counterparty**, **directory_place**, **directory_standard**, **directory_radius**, **directory_reasons** — справочники (как правило id, name; у части — parentFields, price, standard, self_employed, verme_id, external_id, bank_name, web, position, full_name, kpp, brand_name и т.д.).

### 4.4 Заказы, задачи, ставки, запросы

- **orders** — заказы: id, place_id, user_id, self_employed, status, accept_user_id, external_id, external_type, timestamps.
- **order_activities** — активности заказа: order_id, view_activity_id, count, date_start, date_end, need_foto, date_activity.
- **accept_order** — принятие заказа (order_id, user_id).
- **tasks** — задачи: id, place_id, user_id, accept_user_id, specialist_user_id, order_id, project_id, status, self_employed, price, income, scope_of_services, external_id, external_type, timestamps.
- **task_activities** — активности задачи (аналогично order_activities).
- **accept_task** — принятие задачи (task_id, user_id, accepted).
- **bids** — ставки: id, place_id, user_id, accept_user_id, order_id, task_id, status, self_employed, radius, price, view_activity_id, count, date_start, date_end, need_foto, date_activity, activity_id, external_id, external_type, timestamps.
- **bid_activities** — активности ставки.
- **accept_bid** — принятие ставки (bid_id, user_id, accepted, task_id, order_id, user_id_maintainer, timestamps).
- **search_request** — поисковый запрос: place_id, user_id, order_id, task_id, status, self_employed, radius, price, view_activity_id, count, date_start, date_end, need_foto, date_activity, activity_id.
- **requests** — заявки специалистов (аналогичная структура).

### 4.5 Отчёты

- **report** — отчёты: id, user_id, bid_id, order_id, task_id, date_start, date_end, status, report (JSON), date_auto_close, dayActivity, forPay, income, coefficient, hours, placeholder, pvp, timestamps.
- **report_reason** — связь отчёт–причина (report_id, reason_id, count, amount).

### 4.6 Документы

- **documents** — документы пользователя: id, uuid, user_id, file_path, file_name, status, status_signature, date_signature, document_id, file_id, file_path_signed.
- **document_templates** — шаблоны документов (number, date_start, date_end и др.).
- **recognition_documents** — распознавание (file_type и др.).

### 4.7 Прочее

- **settings** — настройки (id, key, value, name).
- **user_directory_project**, **user_directory_place**, **user_directory_counterparty** — связи пользователь–проект/место/контрагент.
- **manager_supervisor** — менеджер–супервайзер (user_id_manager, user_id_supervisor).
- **manager_specialist**, **supervisor_specialist** — менеджер/супервайзер – специалист.
- **user_contract_data** — данные контракта пользователя.
- **cache**, **cache_locks** — кеш.
- **jobs**, **job_batches**, **failed_jobs** — очереди.

Полный список файлов миграций: в `database/migrations/` около 149 файлов; при необходимости точное описание колонок можно взять из каждого файла.

---

## 5. Модели (app/Models)

### 5.1 User (app/Models/User.php)

- **Fillable:** name, email, password, api_token, phone, data, img, confirmRegister, pin, finishRegister, expansionData, errorData, estateData, requisitesData, mapAddress, mapRadius, updateData, change_fields, date_for_send, uuid, register_hash, флаги (repeat_bid, leave_bid, refusal_task, waiting_task и др.), latitude, longitude, count_wait_bid, time_answer_bid, notification_start, verme_id, nopaper_guid, nopaper_certificate_id, time_book_guid.
- **Relations:** roles (BelongsToMany), orders, bids (HasMany), acceptOrder, acceptBid, acceptTask (HasMany), acceptedOrders, acceptedTasks, acceptedBids (BelongsToMany через pivot), project, place, counterparty (BelongsToMany), supervisors, manager, managerSpecialist, supervisorSpecialist (BelongsToMany User).
- **Логика:** HasApiTokens (Passport), Notifiable; isAdmin(); generateToken(), checkToken($api_token); зарегистрирован UserObserver.

### 5.2 User\Role, User\UserRole, User\UserContractData, User\UserUpdates

- Роль (name), связь пользователь–роль, контрактные данные и обновления пользователя.

### 5.3 Order\Order (OrderInterface)

- **Fillable:** place_id, user_id, self_employed, status, accept_user_id, external_id, external_type.
- **Casts:** status → OrderStatusEnum.
- **Relations:** user, acceptUser, place (BelongsTo); orderActivities (HasMany); viewActivities (BelongsToMany через order_activities); bids, tasks (HasMany); acceptingUsers (BelongsToMany accept_order).

### 5.4 Order\OrderActivities, Order\BidActivity, Order\TaskActivity

- Связи активностей заказа, ставки и задачи с view_activities и pivot-полями (count, date_start, date_end, need_foto, date_activity).

### 5.5 Order\Bid (OrderInterface)

- **Fillable:** place_id, user_id, accept_user_id, order_id, task_id, status, self_employed, radius, price, view_activity_id, count, date_start, date_end, need_foto, date_activity, activity_id, external_id, external_type.
- **Casts:** status → OrderStatusEnum, date_start/date_end → datetime, date_activity → json.
- **Relations:** place, user, acceptUser, order, task, viewActivity (BelongsTo); bidActivities (HasMany); acceptingUsers (BelongsToMany accept_bid с pivot accepted, task_id, order_id, user_id_maintainer).

### 5.6 Order\Task (OrderInterface)

- **Fillable:** place_id, user_id, accept_user_id, specialist_user_id, order_id, status, self_employed, price, income, scope_of_services, project_id, external_id, external_type.
- **Casts:** status → OrderStatusEnum.
- **Relations:** place, project, user, acceptUser, specialistUser, order (BelongsTo); bid (HasMany); taskActivities (HasMany); viewActivities (BelongsToMany); acceptingUsers (BelongsToMany accept_task).

### 5.7 Order\Report

- **Fillable:** user_id, bid_id, order_id, task_id, date_start, date_end, status, report, date_auto_close, dayActivity, forPay, income, coefficient, hours, placeholder, pvp.
- **Casts:** status → ReportStatusEnum, report → json, даты → datetime.
- **Relations:** order, task, bid, user (BelongsTo); reasons (BelongsToMany report_reason с pivot count).
- **Методы:** getReasonsAmount() — сумма по причинам.

### 5.8 Order\SearchRequest, Order\Request

- Поисковый запрос и заявка специалиста; связи с place, user, order, task, viewActivity; status → OrderStatusEnum у Request.

### 5.9 Document\Document

- **Fillable:** uuid, user_id, file_path, file_name, status, status_signature, date_signature, document_id, file_id, file_path_signed.
- **Casts:** status → DocumentStatusEnum, status_signature → DocumentStatusSignatureEnum, date_signature → datetime.

### 5.10 Document\DocumentTemplate, Document\RecognitionDocument

- Шаблоны документов и записи распознавания.

### 5.11 Setting

- **Fillable:** key, value, name. Статический метод getValue(string $key).

### 5.12 Fields\Fields

- **Fillable:** uuid, name, description, parentFields, type, directory, active, step, sort, label, heading, placeholder, dividerTop/Bottom, helperInfo*, drawerInfo*, section, estate, requisites, screen, role_id.
- **Relations:** roles (BelongsToMany fields_user_role).

### 5.13 Fields\Directory\* (справочники)

- **Country, Bank, Activities, TaxStatus, Citizenship, Residence, RegionOfResidence, OfferSearch, ViewActivities, Weight, Height, ShoeSize, ClothingSize, HairColor, HairLength, Gender, Messengers, Documentation, Organization, Age, MedicalBook, Project, Brand, Counterparty, Place, Standard, Radius, Reasons** — типично id, name, часто parentFields; у части — price, standard, self_employed, verme_id, external_id и др. Place связан с Project (BelongsToMany), RegionOfResidence (BelongsTo); метод getForUserQr(). Интерфейс ModelDirectoryInterface для справочников.

### 5.14 Certificates, Fields\Directory\Payouts

- Модели сертификатов и выплат (по использованию в коде).

---

## 6. Контроллеры (app/Http/Controllers)

### 6.1 Admin

- **Auth\LoginController** — customAdminLogin (вход в админку), logout.
- **Page\MainPageController** — mainPage (главная админки).
- **Page\UsersController** — usersCreate, usersCreateAjax, usersList, userEdit, userEditAjax, userDelete (CRUD пользователей).
- **Page\Fields\FieldsController** — fieldsCreate, fieldsCreateAjax, fieldsList, fieldsEdit, fieldsEditAjax, fieldsDelete (CRUD полей форм).
- **Page\Fields\Directory\*** — для каждого справочника: create, createAjax, list, edit, editAjax, delete (CountryController, BankController, ActivitiesController и т.д.).
- **Import\ImportController** — index, import, importSave (импорт справочников).
- **Settings\SettingsController** — index, save (настройки админки).
- **Certificates\CertificatesController** — index, save (сертификаты).
- **QrCode\QrCodeController** — index, getBindings, createUserLink (QR и привязки).
- **ForTest\OrderController, TaskController, BidController** — list, delete (тестовые заказы/задачи/ставки).
- **Document\DocumentController** — index (test), save, checkDocument, create, createAjax, list, edit, editAjax, delete (документы и шаблоны).

### 6.2 Form

- **RegistrationController** — sendPhone, getUserByHash, checkCode, setUserEmail, checkEmailCode, setUserPin, startRestorePin, checkCodeRestore (регистрация по телефону/email, восстановление PIN).
- **FormController** — getUserInfo, getForm, saveForm, saveUserImg, finishRegister, saveFile (анкета и шаги регистрации).

### 6.3 PersonalArea

- **UserPersonalInfoController** — getUserInfo, getUserFields, getUserPersonalMenu, saveUserFields, saveUserImg, setUserEmail, checkEmailCode, changeUserPhone, confirmChangeUserPhone, getRequisitesData, getEstateData, saveRequisitesData, saveEstateData, deleteRequisite, deleteEstate, getFormActivities, saveUserFieldsActivities, getBic, getMapField, setMapField.
- **DocumentsController** — getDocumentSigned, getDocumentArchive, getDocumentInquiries, getDocumentConclude, getDocumentTerminate, setConclude, setTerminate, getCompanyAndCertificatesInquiries, requestInquiries, createDocument.
- **CheckPinController** — checkPin, refreshToken.

### 6.4 UserRoles

- **ClientController** — работа с брендом, местами, данными пользователя; getOrders, getOrder, acceptOrder, createOrder, cancelOrder, sendOrder, updateOrder, активности заказа, getViewActivitiesForOrder, repeatOrder (через OrderRepository).
- **ManagerController** — заказы, задачи (convertTask, createTask, updateTask, активности, instructTask, invoiceTask, cancelTask, repeatTask), ставки (getBids, getBid, invoiceBid, cancelBid, acceptSpecialist, declinedSpecialist, updateBid), работы и отчёты (getJobs, getJob, endJob, acceptReport, acceptAllReportJob, payReport, updateReport, getReasons), супервайзеры (getSurepvisorData).
- **SupervisorController** — перекрывает часть логики менеджера (payReport, updateReport, getReasons, endJob и т.д.).
- **RecruiterController** — getRequests, getRequest, acceptRequest (заявки специалистов).
- **SpecialistController** — acceptBid, rejectBid, startDay, endDay, endJob, payReport (отметка оплаты); getCounterpartiesForSign, signContracts; signedDocuments, signedDocumentsSendCode, signedDocumentsRetriesSms, getSignetDocument (Nopaper).

### 6.5 UniversalController

- Роутер по ролям: getData; getBrand, setBrandImg, getPlace, setPlace, delPlace, setUserData (client/manager/recruiter/supervisor); getOrders, getOrder, acceptOrder; getTasks, getTask, acceptTask; createSearchFromOrder, createSearchFromTask, updateSearch; getBids, getBid, invoiceBid, cancelBid, acceptSpecialist, declinedSpecialist, getSpecialistForBid, updateBid; getPlaceForOrder, getPlaceForTask, getPlaceForBid, getRadiusSelect; getJobs, getJob, endSpecialistJob; acceptReport, acceptAllReportJob, payReport, updateReport, getReasons; getManager, setManagers, delManager; блок moderation (проекты, места, клиенты, confirmUserRegister, setUserImg, супервайзеры, контрагенты). Вызовы делегируются в соответствующий контроллер по роли пользователя.

### 6.6 Integration, Settings

- **Integration\IntegrationController** — ping, updateUserData (внешние системы по CheckIntegration).
- **Settings\SettingsController** — getFromKey, getAll (API настроек для приложения).

---

## 7. Сервисы (app/Services)

### 7.1 Регистрация и авторизация

- **ApiTokenService\ApiTokenService** — createToken(scopes), refreshToken(refreshToken); удаление старых токенов пользователя, выдача Passport Personal Access Token.
- **Register\SmsCodeService** — createCode(), checkCode() (SMS-код для входа/регистрации).
- **Register\SmsService** — sendCode(phone, code) (отправка SMS).
- **Register\EmailService, Register\EmailVerifiedService** — работа с email при регистрации/верификации.

### 7.2 Документы и ЭДО

- **Nopaper\NopaperService** — sendDocumentsToNopaper, confirmSms, retriesSms, registerUser, checkUserExists, checkCompanyExists, createDraft, attachFileToDocument, sendDocument, getDocumentInfo (интеграция с Nopaper).
- **DocumentServices\CorrectRecognitionService** — createPackage, uploadImage, startRecognition, getRecognitionResult, ping (распознавание документов).
- **DocumentServices\RecognitionDocumentService** — работа с моделью распознавания.
- **DocumentCreator\UserDocumentCreatorService, DocumentCreator\PdfCreatorService** — создание документов/PDF для пользователя.
- **CreatePdfFileService** — генерация PDF.
- **User\DataForDocumentCreatorService** — данные пользователя для генерации документов.

### 7.3 PVP (учёт времени/смен)

- **PVP\PVPService** — обёртка над PVPAbstract: startLoad(), getResultData(user, bid), getUser(place), getPlace(prefix, place), getJob(prefix, job), assignToShift(user, guid), getDataWork(user, bid).
- **PVP\PVPAbstract** — базовый класс провайдеров.
- **PVP\XFive\XFiveService**, **PVP\Verme\VermeService**, **PVP\TimeBook\TimeBookService** — провайдеры XFive, Verme, TimeBook (getTimesheets, assignToShift, createEmployee и т.д.).

### 7.4 Репозитории

- **Local\Repositories\Order\EloquentOrderRepository** — реализация OrderRepository: полная работа с Order/Task/Bid/SearchRequest/Request/Report (создание, обновление, отмена, принятие, инструктаж, счёт, отчёт, повтор заказа/задачи и т.д.).
- **Local\Repositories\Order\CachingOrderRepository** — обёртка с кешем над OrderRepository.
- **Local\Repositories\User\EloquentUserRepository** — getModerationUsers, getModerationUsersPaginate, getModerationUser (UserRepository).
- **Local\Repositories\User\CachingUserRepository** — кеш над UserRepository.

### 7.5 Пользователь и формы

- **User\UserDataService** — getName, getOnlyName, getShortName, getTaxStatusName, getTaxStatusTemplate и др. (данные для документов и отображения).
- **FormBuilderService** — setDataUser, createFormData, createPersonalUserFormData, getStepField, checkStatusForm, checkRequired, getUserField (построение и валидация форм по шагам и полям).

### 7.6 Интеграции и утилиты

- **OneC\OneCServices, OneC\OneCServicesClient** — sendRegister, updateUserData, sendUpdateUserRequisites, setTerminate, setConclude, requestInquiries, getUserRequisites, ping, sendPost, sendGet (интеграция с 1С).
- **TimeService** — работа с датой/временем.
- **CoordinatesService** — координаты (карта/радиус).
- **Formatter\*** — форматтеры полей форм (Photo, Text, Select, Date, Phone, Email, Bic, Inn, Snils, Card, File, Map, Month, Sms, Checkbox, Radio, Autocomplete и т.д.) для вывода и валидации.

---

## 8. Бизнес-логика и сценарии

### 8.1 Роли и доступ

- **Роли:** admin, client, manager, recruiter, supervisor, specialist. Связь User–Role через user_roles. Admin — доступ в админку (CheckPermission). В API — CheckRole по имени роли; scope Passport: register, personalArea, checkPin, restorePin.

### 8.2 Цепочка заказа

1. **Client** создаёт **Order** (место, активности, даты).
2. **Manager** принимает заказ (acceptOrder) и создаёт **Task** (проект, супервайзеры).
3. Создаётся **SearchRequest** (createSearchFromOrder / createSearchFromTask).
4. Специалисты получают **Request**; **Recruiter** может принять заявку (acceptRequest).
5. **Specialist** принимает/отклоняет **Bid** (acceptBid, rejectBid).
6. **Manager/Supervisor** выставляет счёт (invoiceBid), назначает специалиста (acceptSpecialist), инструктаж (instructTask).
7. **Specialist** startDay / endDay, отчитывается → **Report** (статусы: start → end → reported → accept → forPay → paid).
8. **Manager/Supervisor** — acceptReport, updateReport, payReport; **Specialist** — payReportSpecialist (отметка оплаты).

### 8.3 Регистрация и авторизация

- По ссылке с hash (getUserByHash) или по телефону (sendPhone). Если пользователь есть: вход по PIN (checkPin) или продолжение регистрации/модерации (SMS-код, finishRegister). Токены — Passport; scope в зависимости от этапа (register → checkPin → personalArea). PIN для входа в личный кабинет; восстановление PIN — startRestorePin, checkCodeRestore. Email — setUserEmail, checkEmailCode.

### 8.4 Документы и ЭДО

- Документы пользователя: подписанные, архив, запросы, заключение/расторжение (setConclude, setTerminate). Nopaper: регистрация, отправка на подпись, подтверждение SMS. В админке — шаблоны документов, создание/редактирование по типам. Распознавание (CorrectRecognitionService) — пакет, загрузка изображения, старт распознавания, результат.

### 8.5 Интеграции

- **1C (OneCServices):** регистрация пользователя, обновление реквизитов, заключение/расторжение договоров, запросы справок.
- **PVP (XFive, Verme, TimeBook):** загрузка смен/демандов, привязка пользователя к смене (assignToShift), таймшиты для расчёта часов/оплаты.
- **Integration (ping, updateUserData):** внешние системы по middleware CheckIntegration.

### 8.6 Модерация

- confirmUserRegister, управление проектами и местами (getProject, setProject, delProject, getPlaceModeration, setPlaceModeration, delPlaceModeration), супервайзеры и контрагенты (getSurepvisors, setSurepvisors, delSurepvisor, getCounterparty, setCounterparty, deleteCounterparty), setUserImg. Списки на модерацию — getModerationClient, getModerationSingleClient.

---

## 9. Консольные команды и расписание (routes/console.php)

| Команда | Расписание | Назначение |
|---------|------------|------------|
| endedReportCommand | everyFiveMinutes | Завершение отчётов |
| dellSpecialistFromManagerAndSupervisorCommand | daily | Удаление специалиста из связей с менеджером/супервайзером |
| closeBidCommand | hourly | Закрытие ставок |
| sendUserFileToCorrect | everyThreeMinutes | Отправка файлов на распознавание |
| getUserFileFromCorrect | everyThreeMinutes | Получение результатов распознавания |
| getUserReportCoefficient | everyMinute | Коэффициенты отчётов |
| archiveBid | everyThreeMinutes | Архивация ставок |
| archiveTask | everyThreeMinutes | Архивация задач |
| archiveOrder | everyThreeMinutes | Архивация заказов |
| inWorkBid | everyThreeMinutes | Перевод ставок в работу |
| archiveJob | everyThreeMinutes | Архивация работ |

Дополнительные команды в `app/Console/Commands/`: LoadOrderXFiveCommand, LoadOrderTimeBookCommand, LoadOrderVermeCommand (загрузка из PVP), ProcessRecognitionDocuments, DellAllUpdateDataCommand, SendUpdateDataUserCommand, TestCommand и др.

---

## 10. Middleware

- **CheckPermission** — доступ в админку (проверка роли admin).
- **CheckRole** — проверка роли в API (client, manager, recruiter, supervisor, specialist, admin).
- **CheckIntegration** — проверка ключа/подписи для маршрутов интеграции (ping, updateUserData).

---

Документация составлена по состоянию кода проекта. При изменении маршрутов, миграций, моделей или сервисов файл стоит обновить.
