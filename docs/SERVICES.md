# Детальная документация сервисов

Описание назначения, зависимостей, методов и потока данных для каждого сервиса.

---

## 1. ApiTokenService (app/Services/ApiTokenService/ApiTokenService.php)

**Назначение:** Выдача и обновление OAuth2-токенов Laravel Passport для API.

**Зависимости:** User, трейт PassportToken (getBearerTokenByUser), config passport.personal_access_client (id, secret).

### Методы

- **createToken(array $scopes = [])**  
  Удаляет все существующие токены пользователя (`delUserTokens`), затем создаёт новый Personal Access Token через `getBearerTokenByUser($this->user, client_id, $scopes, false)`. Возвращает объект с `token_type`, `expires_in`, `access_token`, `refresh_token`.

- **delUserTokens()**  
  Проходит по `$this->user->tokens` и удаляет каждый токен (revoke/delete).

- **refreshToken($refreshToken)** (статический)  
  Отправляет POST на `config('app.url').'/oauth/token'` с `grant_type=refresh_token`, `refresh_token`, `client_id`, `client_secret`, `scope=personalArea`. По ответу парсит JWT access_token, извлекает jti, находит запись в `oauth_access_tokens`, по user_id находит User. Затем создаёт новый токен с scope `checkPin` через `(new self($user))->createToken(['checkPin'])` и возвращает этот объект токена (или сырой ответ от OAuth при ошибке).

**Использование:** RegistrationController (sendPhone, checkCode, setUserPin, startRestorePin, checkCodeRestore), FormController (finishRegister), CheckPinController (checkPin, refreshToken).

---

## 2. Register\SmsCodeService (app/Services/Register/SmsCodeService.php)

**Назначение:** Генерация и проверка SMS-кода при регистрации/входе. Хранение кода в Redis по ключу phone.

**Зависимости:** Redis, SmsService (отправка SMS).

### Конструктор

- **__construct(string $phone, int $code = 0)**  
  Сохраняет phone. Если code не передан — по умолчанию 1111 (для тестов); иначе используется переданный код.

### Методы

- **createCode(): array**  
  Проверяет `Redis::exists($this->phone)`. Если ключ есть — возвращает `['status'=>'exists','ttl'=>Redis::ttl($this->phone)]`, устанавливает `$this->status = 'error'`. Иначе вызывает `SmsService::sendCode($this->phone, $this->code)`. При успехе отправки: `Redis::set($this->phone, $this->code, 'EX', 120)`, возвращает `['status'=>'success','code'=>$this->code,'ttl'=>120]`. При ошибке отправки — `['status'=>'errorSend']`, `$this->status = 'error'`.

- **checkCode(): array**  
  Если `Redis::exists($this->phone)`: сравнивает значение с `$this->code`. При совпадении — `Redis::del($this->phone)`, возвращает `['status'=>'success']`. Иначе `['status'=>'error']`. Если ключа нет — `['status'=>'notExists']`.

**Использование:** RegistrationController (sendPhone, checkCode, startRestorePin, checkCodeRestore).

---

## 3. Register\SmsService (app/Services/Register/SmsService.php)

**Назначение:** Отправка SMS (реализация канала: заглушка или интеграция с провайдером).

**Метод:** sendCode(phone, code) — возвращает true/false в зависимости от успеха отправки.

---

## 4. Register\EmailService, EmailVerifiedService

**Назначение:** Отправка писем и верификация email (код подтверждения, обновление user->email при успехе). Используются в регистрации и смене email в личном кабинете.

---

## 5. FormBuilderService (app/Services/FormBuilderService.php)

**Назначение:** Построение и валидация данных форм регистрации/профиля по шагам и полям из таблицы fields.

**Зависимости:** Fields, PersonalInfoSectionEnum, FieldsTypeEnum, Auth (для ролей при фильтрации полей).

### Основные свойства

- step, formData, formDataThisStep — текущий шаг и данные формы (по шагам).
- directory, filterArr, fieldsAll, fieldsThisStep, fieldsOldStep — поля и фильтры.
- formatedData, moreData, errorData, updateData — результат форматирования и доп. данные.

### Конструктор

- **__construct(int $step, array $formData = [])**  
  Нормализует formData: объединяет шаги в один массив (array_merge) или обрабатывает исключения; formDataThisStep = formData[$step] или весь formData.

### Методы

- **setDataUser(array $moreData, array $errorData, array $updateData, array $changefields)**  
  Устанавливает errorData, moreData, updateData и мержит changefields с formData и formDataThisStep.

- **createFormData(array $moreData = [], array $errorData = [], array $updateData = []): array**  
  Вызывает getFilterArr(), getFields(), filterFields(), затем formatData($this->fieldsThisStep). Возвращает массив полей для отображения на шаге с отформатированными значениями и подсказками об ошибках.

- **createPersonalUserFormData($section): array**  
  То же для раздела профиля (не по шагу, а по секции): getAllFields($section), filterFields(), formatData(..., true).

- **getStepField()**  
  Заполняет filterArr, fields, filterFields() без возврата (для проверки доступности шага).

- **checkStatusForm(bool $param = false)**  
  Проверяет обязательные поля и заполненность. Возвращает тип: needRequired | allowedNewStep | addedNewFields (и т.п.) в зависимости от валидности и параметра.

- **getUserField($uuid)**  
  Возвращает значение поля по uuid из formData.

Внутренние: getFilterArr() — из formData собирает массив значений для фильтрации дочерних полей; getFields() / getAllFields() — выборка полей из БД по step/section и role; filterFields() — отбор полей по parentFields (условия показа); formatData() — для каждого поля вызывается соответствующий Formatter по типу (Photo, Text, Select, Date, Phone и т.д.), подставляются value, error, label.

**Использование:** FormController (getForm, saveForm), UserPersonalInfoController (getUserFields, saveUserFields и др.).

---

## 6. Local\Repositories\Order (OrderRepository)

**Интерфейс:** app/Services/Local/Repositories/Contracts/OrderRepository.php.

**Реализация:** EloquentOrderRepository (app/Services/Local/Repositories/Order/EloquentOrderRepository.php). Обёртка с кешем: CachingOrderRepository.

### Назначение

Единая точка работы с заказами, задачами, ставками, поисковыми запросами, заявками и отчётами: создание, обновление, смена статусов, привязка пользователей.

### Основные методы (логика в EloquentOrderRepository)

- **createOrder(CreateOrderRequest, userId): Order**  
  Создаёт Order (place_id, user_id, self_employed, status=new). Возвращает модель.

- **createOrderActivity(CreateOrderActivityRequest): Order**  
  Создаёт запись OrderActivities (view_activity_id, count, date_start, date_end, need_foto, date_activity, order_id). Возвращает заказ.

- **updateOrder(UpdateOrderRequest): Order**  
  Обновляет заказ (place_id, self_employed), при смене места удаляет активности, не входящие в новые view_activities места. Транзакция.

- **deleteOrderActivity(DeleteOrderActivityRequest): Order**  
  Удаляет одну активность заказа по id и order_id.

- **getUserOrderByStatusPaginate(?OrderStatusEnum, userId): Collection**  
  Заказы пользователя по статусу с пагинацией.

- **getUserOrderByStatus(userId, orderId|null): Order|null**  
  Один заказ пользователя или список по статусу.

- **cancelOrder(orderId): bool**  
  Смена статуса заказа на отменён.

- **sendOrder(orderId): bool**  
  Смена статуса на «отправлен» (доступен менеджеру).

- **createTask(CreateTaskRequest, userId): Task**  
  Создаёт Task (place_id, project_id, user_id, self_employed, status=new, specialist_user_id=null, accept_user_id=null, order_id=null, price/income/scope=0).

- **createTaskActivity(CreateTaskActivityRequest): Task**  
  Создаёт TaskActivity, возвращает задачу.

- **updateTask, deleteTaskActivity, updateTaskActivity**  
  Обновление задачи и её активностей.

- **acceptedOrder(User, orderId): bool**  
  Запись в accept_order и обновление order.accept_user_id (менеджер принял заказ).

- **convertTask(User, ConvertTaskRequest): Task**  
  Создание задачи из заказа: копирование места, активностей, привязка order_id, создание SearchRequest и т.д.

- **getTaskByUserSyncDataPaginate, getTaskByUserSyncData**  
  Задачи по данным пользователя (места/проекты менеджера) с фильтром по статусу.

- **instructTask(taskId, supervisorIds): bool**  
  Инструктаж по задаче (привязка супервайзеров, смена статуса).

- **invoiceTask(taskId, supervisorId): bool**  
  Выставление счёта по задаче.

- **cancelTask(taskId): bool**  
  Отмена задачи.

- **acceptTask(User, taskId): bool**  
  Запись в accept_task, обновление task.accept_user_id.

- **createBidFromOrder(User, orderId, orderActivityId): Bid**  
  Создание ставки из заказа (копирование места, активности, привязка order_id).

- **createSearchFromOrder(User, orderId, orderActivityId): SearchRequest**  
  Создание поискового запроса из заказа.

- **createBidFromTask(User, taskId, taskActivityId): Bid**  
  Аналогично из задачи.

- **createSearchFromTask(User, taskId, taskActivityId): SearchRequest**  
  Поисковый запрос из задачи.

- **getBidsByUserSyncDataPaginate, getBidByUserSyncData**  
  Ставки по местам/задачам/заказам пользователя.

- **invoiceBid(bidId, specialistIds): bool**  
  Выставление счёта по ставке, выбор специалиста.

- **acceptBid(User, bidId): bool**  
  Специалист принимает ставку: запись в accept_bid (accepted=true), смена статуса bid, создание Report при необходимости.

- **rejectBid(User, bidId): bool**  
  Отклонение ставки специалистом.

- **instructBid(bidId, specialistId): bool**  
  Назначение специалиста на ставку (инструктаж).

- **cancelBid(bidId): bool**  
  Отмена ставки.

- **getSpecialistForBid(bidId): Collection**  
  Пользователи из accept_bid по bid_id.

- **updateBid(BidDataRequest): Bid**  
  Обновление полей ставки (цена, даты, активности и т.д.).

- **updateSearch(SearchDataRequest): SearchRequest**  
  Обновление поискового запроса.

- **getJobsByUserSyncDataPaginate(User, specialistId = null): Collection**  
  «Работы» — принятые ставки (accept_bid.accepted) с фильтром по пользователю.

- **getJobByUser(GetJobRequest): Bid**  
  Одна ставка-работа по id.

- **endSpecialistJob**  
  Завершение работы: смена статусов bid/report.

- **repeatOrder(RepeatOrderRequest): Order**  
  Копирование заказа с новыми датами/активностями.

- **repeatTask(RepeatTaskRequest): Task**  
  Копирование задачи.

Отчёты (acceptReport, updateReport, payReport и т.д.) работают с моделью Report и связью report_reason; смена статусов (reported, accept, forPay, paid). Всё через Eloquent и транзакции где нужно.

**Использование:** ClientController, ManagerController, SupervisorController, SpecialistController, UniversalController (через эти контроллеры).

---

## 7. Local\Repositories\User (UserRepository)

**Интерфейс:** UserRepository.  
**Реализации:** EloquentUserRepository, CachingUserRepository.

**Методы:** getModerationUsers, getModerationUsersPaginate, getModerationUser — выборка пользователей на модерации (по finishRegister, confirmRegister и т.д.), с фильтрами и пагинацией.

**Использование:** ManagerController (модерация клиентов, супервайзеры).

---

## 8. Nopaper\NopaperService (app/Services/Nopaper/NopaperService.php)

**Назначение:** Интеграция с Nopaper для ЭДО: регистрация пользователя, создание черновика документа, прикрепление файлов, отправка на подпись, подтверждение по SMS.

**Конфиг:** services.nopaper.base_url, services.nopaper.api_key.

### Основные методы

- **sendDocumentsToNopaper(User): bool**  
  Проверяет checkUserExists(user). Создаёт черновик (createDraft) с recipientInfoList (телефон, actionType, SignType). Для каждого подписанного документа пользователя (Document status=Signed, status_signature=NoSend) прикрепляет файл (attachFileToDocument), затем sendDocument(documentId). Обновляет документы: status_signature=Process, document_id. Отправляет SMS пользователю (sendSms). Возвращает true при успехе.

- **confirmSms(User, documentId, code)**  
  Подтверждение кода из SMS для документа.

- **retriesSms(User, documentId)**  
  Повторная отправка SMS.

- **registerUser(User)**  
  Регистрация пользователя в Nopaper (получение nopaper_guid и т.д.).

- **checkUserExists(User), checkCompanyExists(...)**  
  Проверка существования пользователя/компании в Nopaper.

- **createDraft(recipientInfoList)**  
  POST к API создания черновика, возврат documentId.

- **attachFileToDocument(documentId, fileData)**  
  Прикрепление файла (base64), возврат fileId.

- **sendDocument(documentId): bool**  
  Отправка документа на подпись.

- **getDocumentInfo(documentId)**  
  Получение статуса документа.

**Использование:** SpecialistController (signedDocuments, sendCode, retriesSms), DocumentsController, админка/команды при массовой отправке.

---

## 9. DocumentServices\CorrectRecognitionService (app/Services/DocumentServices/CorrectRecognitionService.php)

**Назначение:** Распознавание документов через API Correct (extractor.correct.su). Пакет изображений → распознавание → результат.

**Конфиг:** services.correct_recognition.token.

### Методы

- **createPackage(?catalogId = null): ?int**  
  POST /api/packages. Возвращает packageId.

- **uploadImage(packageId, imagePath): ?int**  
  Загрузка файла из Storage (public) в пакет. POST с attach. Возвращает imageIds[0].

- **startRecognition(packageId, ?callbackUrl): bool**  
  POST /api/packages/{id}/start. Запуск распознавания. Возвращает true при 204.

- **getRecognitionResult(packageId): ?array**  
  GET /api/packages/{id}. Возвращает JSON ответа или null.

- **ping(): bool**  
  GET /api/packages/ping. Проверка доступности API.

**Использование:** Команды SendUserFileToCorrect, GetUserFileFromCorrect; DocumentServices\RecognitionDocumentService при создании записей распознавания.

---

## 10. DocumentServices\RecognitionDocumentService

**Назначение:** Создание записей в recognition_documents по данным пользователя и вызов CorrectRecognitionService (createPackage, uploadImage, startRecognition). Связь с User и Document. После получения результата (по callback или по запросу) — разбор полей и обновление user->data или документов.

**Использование:** FormController::finishRegister (createDocumentForRecognition после отправки в 1С).

---

## 11. DocumentCreator\UserDocumentCreatorService, PdfCreatorService

**Назначение:** Формирование документа (PDF/Word) для пользователя: подстановка данных из User и DataForDocumentCreatorService в шаблон (document_templates), генерация файла. UserDocumentCreatorService координирует тип документа и вызов PdfCreatorService или иных генераторов.

**Использование:** Создание документов в ЛК, админка, Nopaper (исходный файл для подписи).

---

## 12. CreatePdfFileService (app/Services/CreatePdfFileService.php)

**Назначение:** Приём загруженных файлов (массив), сохранение в storage, при необходимости слияние в один PDF (например по fieldUuid и phone пользователя). Свойства: mergeFilePath (путь к результирующему файлу), error (текст ошибки).

**Использование:** FormController::saveFile.

---

## 13. User\DataForDocumentCreatorService

**Назначение:** Подготовка данных пользователя (ФИО, паспорт, адрес, реквизиты и т.д.) в формате, удобном для подстановки в шаблоны документов. Использует UserDataService и поля user->data, requisitesData, estateData.

---

## 14. User\UserDataService (app/Services/User/UserDataService.php)

**Назначение:** Геттеры для отображения данных пользователя: getName, getShortName, getTaxStatusName, getPassportDetails, getRegistrationAddress и т.д. Чтение из user->data (JSON) и связанных справочников (например TaxStatus). Используется в генерации документов и в API.

---

## 15. PVP\PVPService (app/Services/PVP/PVPService.php)

**Назначение:** Общая обёртка над провайдерами учёта времени/смен (XFive, Verme, TimeBook). Загрузка заказов из PVP в систему, привязка пользователя к смене, получение таймшитов для расчёта часов в отчётах.

**Зависимости:** PVPAbstract (реализации: XFiveService, VermeService, TimeBookService), модели Order, OrderActivities, User, Place, ViewActivities, RoleEnum.

### Методы

- **startLoad()**  
  Получает данные из PVP через `$this->pvp->getData()`. Для каждой записи, если заказа с таким external_id ещё нет: находит Place по external_id (getPlace(prefix, place)), ViewActivities по getJob(prefix, job), User через getUser(place) (клиент по проекту места или первый клиент). Создаёт Order (place_id, external_id, user_id, self_employed, status=notAccepted, external_type) и OrderActivities (view_activity_id, count, date_start, date_end, need_foto). Таким образом заказы из PVP попадают в систему как «не принятые».

- **getResultData(User, Bid)**  
  Делегирует `$this->pvp->getTimesheets($user, $bid)` — получение данных по отработанным часам для отчёта.

- **getUser(Place): User**  
  Ищет проект места; если есть — пользователя с ролью client и привязкой к этому проекту; иначе первого пользователя с ролью client.

- **getPlace(prefix, place): ?Place**  
  Place::where('external_id', $prefix.$place)->first().

- **getJob(prefix, job): ?ViewActivities**  
  ViewActivities::where('external_id', $prefix.$job)->first().

- **assignToShift(User, guid)**  
  Вызов `$this->pvp->assignToShift($user, $guid)` — привязка сотрудника к смене в PVP.

- **getDataWork(User, Bid)**  
  То же что getResultData — getTimesheets.

- **static getServiceObject($namePvp): ?self**  
  Создаёт PVPService с экземпляром переданного класса провайдера (XFiveService::class и т.д.).

**Провайдеры (PVPAbstract):**

- **XFive\XFiveService** — методы getData (загрузка заказов/смен), getTimesheets, assignToShift, getPrefix, getType. Конфиг/API XFive.
- **Verme\VermeService** — getTimesheets, getResultData, createEmployee, getShifts, assignToShift, registerUser, getData, getPrefix, getType. Интеграция с Verme.
- **TimeBook\TimeBookService** — authenticate, createOrganization, createStaffPosition, createSubdivision, createEmployee, getDemands, getTimesheets, assignToShift, cancelAssignment, createWebhookSubscription, getData, getPrefix, getType. Интеграция с TimeBook.

**Использование:** Консольные команды LoadOrderXFiveCommand, LoadOrderVermeCommand, LoadOrderTimeBookCommand; расчёт часов в отчётах (getResultData/getTimesheets); назначение на смену (assignToShift) из ЛК или команд.

---

## 16. OneC\OneCServices (app/Services/OneC/OneCServices.php)

**Назначение:** Интеграция с 1С: регистрация пользователя, обновление данных и реквизитов, заключение/расторжение договоров, запросы справок.

**Зависимости:** User, OneCServicesClient (реальный HTTP-клиент к 1С).

### Методы

- **sendRegister(): static**  
  Вызов oneCServicesClient->sendRegister(). Устанавливает $this->status и $this->uuid. Используется при finishRegister для получения UUID в 1С.

- **updateUserData(array)**  
  Отправка обновлённых полей пользователя в 1С.

- **sendUpdateUserRequisites(array)**  
  Обновление реквизитов в 1С.

- **setTerminate(array)**  
  Расторжение договора в 1С.

- **setConclude(array)**  
  Заключение договора в 1С.

- **requestInquiries(array)**  
  Запрос справок в 1С.

- **getUserRequisites()**  
  Получение реквизитов пользователя из 1С.

**Использование:** FormController::finishRegister (sendRegister); PersonalArea\DocumentsController (setConclude, setTerminate, requestInquiries); обновление реквизитов из ЛК.

---

## 17. OneC\OneCServicesClient

**Назначение:** HTTP-клиент к API 1С: sendRegister, sendUpdateUserData, sendUpdateUserRequisites, setTerminate, setConclude, requestInquiries, getUserRequisites, ping, sendPost, sendGet. Конфиг (URL, ключи) в config/services.

---

## 18. TimeService (app/Services/TimeService.php)

**Назначение:** Вспомогательные методы для работы с датами/временем (часовые пояса, форматирование, расчёт интервалов). Используется в отчётах, PVP, документах.

---

## 19. CoordinatesService (app/Services/CoordinatesService.php)

**Назначение:** Работа с координатами (расстояния, проверка вхождения в радиус). Используется для карты (mapAddress, mapRadius, latitude, longitude) и при выборе мест/радиусов.

---

## 20. Formatter (app/Services/Formatter/)

**Назначение:** Форматирование и валидация значений полей форм по типу поля. Каждый тип (Photo, Text, Select, Date, Phone, Email, Bic, Inn, Snils, Card, File, Map, Month, Sms, Checkbox, Radio, Autocomplete, SelectMultiple, CheckboxMultiple, Account и т.д.) реализует FormaterInterface: вывод значения для отображения, подготовка для сохранения, валидация.

**Использование:** FormBuilderService::formatData() при построении formData для шага/секции.

---

Итог: сервисы покрывают авторизацию (токены, SMS, email), формы (FormBuilderService, Formatter), заказы/задачи/ставки/отчёты (OrderRepository), пользователей и модерацию (UserRepository), документы (Nopaper, Correct, PDF, 1С), интеграции (1С, PVP, Correct) и вспомогательные утилиты (Time, Coordinates).
