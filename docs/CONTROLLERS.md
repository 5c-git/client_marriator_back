# Детальная логика контроллеров (методы, вызываемые из роутинга)

Для каждого маршрута указан контроллер, метод, входные данные, что делает метод и что возвращает.

---

## 1. Form\RegistrationController

### getUserByHash (GET /api/getUserByHash/)

- **Вход:** `Request` — query `hash` (обязателен).
- **Логика:** Ищет пользователя по `register_hash`. Если найден — возвращает `userId`, `phone`, `email`, `role` (первая роль). Иначе ошибка «Ссылка недействительна».
- **Ответ:** JSON `{ result, status }` или `{ error, status }`, код 200 или 417.

### sendPhone (POST /api/sendPhone/)

- **Вход:** `phone` (обязателен).
- **Логика:**
  - Если пользователь с таким `phone` есть:
    - Если `confirmRegister` — тип `auth`, создаётся токен с scope `checkPin`, возвращается токен.
    - Если `finishRegister` и не confirm — тип `moderation`, токен `checkPin`.
    - Иначе тип `register` — создаётся SMS-код через `SmsCodeService::createCode()`, очищается `register_hash`, возвращается результат кода (exists/success/errorSend).
  - Если пользователя нет — создаётся новый User (phone, случайный email @mariator.ru, случайный password), привязывается роль specialist, задаётся `mapRadius` из справочника radius (default), тип `register`, отправляется SMS-код.
- **Ответ:** JSON с `result.type`, при регистрации — `result.code`, при auth/moderation — `result.token`.

### checkCode (POST /api/checkCode/)

- **Вход:** `phone`, `code` (обязательны).
- **Логика:** `SmsCodeService($phone, $code)->checkCode()`. При успехе: поиск User по phone, создание токена через `ApiTokenService` (scope `register` или `checkPin` в зависимости от `confirmRegister`/`finishRegister`).
- **Ответ:** JSON с `result.token` при успехе или `result.code` (status error/notExists) при ошибке.

### setUserPin (POST /api/setUserPin/)

- **Вход:** `pin` (обязателен). Требуется scope register, restorePin, checkPin, personalArea.
- **Логика:** Текущий пользователь: `user->pin = $request->pin`, `save()`.
- **Ответ:** JSON `{ status: 'success' }` или ошибка 417 при пустом pin.

### startRestorePin (POST /api/startRestorePin/)

- **Вход:** без тела (телефон берётся из текущего пользователя).
- **Логика:** `SmsCodeService($user->phone)->createCode()`, затем создаётся токен с scope `restorePin`.
- **Ответ:** JSON с `result.code` и `result.token` при success.

### checkCodeRestore (POST /api/checkCodeRestore/)

- **Вход:** `code` (обязателен).
- **Логика:** Проверка кода через `SmsCodeService($user->phone, $code)->checkCode()`. При успехе: `user->pin = null`, сохранение, создание токена (scope `personalArea` или `checkPin` в зависимости от `confirmRegister`).
- **Ответ:** JSON с `result.token` или ошибка кода.

### setUserEmail (POST /api/setUserEmail/) — регистрация

- **Вход:** email. Требуется scope register.
- **Логика:** Установка email пользователю, отправка кода верификации (через EmailService/EmailVerifiedService).
- **Ответ:** JSON success/error.

### checkEmailCode (POST /api/checkEmailCode/) — регистрация

- **Вход:** код из email.
- **Логика:** Проверка кода верификации email, обновление пользователя при успехе.
- **Ответ:** JSON success/error.

---

## 2. Form\FormController

### getUserInfo (GET /api/getUserInfo/)

- **Вход:** авторизация, scope register.
- **Логика:** Текущий пользователь: подмена `img` на полный URL через `Storage::url`, если email не @mariator.ru — отдаётся в ответе, иначе пустая строка. Добавляются роли (RoleResource).
- **Ответ:** JSON `{ result: { userData: { img, email, roles } }, status: 'success' }`.

### getForm (GET /api/getForm/)

- **Вход:** query `step` (опционально, по умолчанию 1). Scope register.
- **Логика:** Если пользователь не `finishRegister`: из `user->data` (JSON по шагам) собираются данные, создаётся `FormBuilderService($step, $userData)`, вызываются `createFormData()` и `checkStatusForm(true)`. Возвращаются formData, step, type (needRequired|allowedNewStep).
- **Ответ:** JSON с formData, step, type; при finishRegister — error.

### saveForm (POST /api/saveForm/)

- **Вход:** `step`, `formData` (опционально). Scope register.
- **Логика:** Если не finishRegister: данные шага записываются в `user->data[$step] = formData`, сохраняется пользователь. Затем заново строится форма для шага, вызывается `checkStatusForm()` без параметра. Возвращаются step и type.
- **Ответ:** JSON `{ result: { step, type }, status: 'success' }` или ошибка.

### saveUserImg (POST /api/saveUserImg/)

- **Вход:** файл `file`. Scope register.
- **Логика:** Загрузка файла в `Storage::disk('public')->putFileAs('/source/userImg/{userId}', ...)`, старый img удаляется, в модель записывается путь, возвращается URL файла.
- **Ответ:** JSON с `resFile` (URL) и status success/error.

### finishRegister (POST /api/finishRegister/)

- **Вход:** без тела. Scope register.
- **Логика:** Вызов `OneCServices($user)->sendRegister()`. При успехе: `user->finishRegister = true`, `user->uuid = $registerResult->uuid`, слияние шагов из `user->data` в один массив, создание документов для распознавания через `RecognitionDocumentService`, сохранение пользователя. Выдача токена с scope `personalArea`.
- **Ответ:** JSON с `result.token` при success или status error.

### saveFile (POST /api/saveFile/)

- **Вход:** `file[]`, `fieldUuid`. Scope register, personalArea.
- **Логика:** Загрузка файлов, создание `CreatePdfFileService($files, $userId, $user->phone, $fieldUuid)`. При успешной генерации возвращается URL слияния (mergeFilePath).
- **Ответ:** JSON с `resFile` и status success/error.

---

## 3. PersonalArea\CheckPinController

### checkPin (POST /api/checkPin/)

- **Вход:** `pin` (обязателен). Scope checkPin.
- **Логика:** Проверка: пользователь есть, `confirmRegister`, pin не пустой и совпадает с переданным. При успехе создаётся токен с scope `personalArea` через ApiTokenService.
- **Ответ:** JSON с `result.token` и status success или error.

### refreshToken (POST /api/refreshToken/)

- **Вход:** `refreshToken` (обязателен). Публичный, throttle.
- **Логика:** Вызов `ApiTokenService::refreshToken($request->refreshToken)` — POST на `/oauth/token` с grant_type refresh_token. При успехе формируется новый токен с scope checkPin для пользователя из access_token.
- **Ответ:** JSON с `result.token` (объект токена или ошибка от OAuth) и status success.

---

## 4. PersonalArea\UserPersonalInfoController

Все методы требуют auth:api и scope personalArea.

### getUserInfo (GET /api/personal/getUserInfo/)

- **Логика:** Текущий пользователь: img как полный URL, email скрыт если @mariator.ru. Удаляются change_fields, date_for_send. Добавляются roles. Возвращается userData (toArray) и roles.
- **Ответ:** JSON `{ result: { userData }, status: 'success' }`.

### getUserFields (GET /api/personal/getUserFields/)

- **Вход:** query `section` (обязателен).
- **Логика:** FormBuilderService с step=10 и данными из user->data. Учитываются expansionData, errorData, updateData, change_fields (JSON decode). Формируются formData по секции через createPersonalUserFormData($section), checkStatusForm.
- **Ответ:** JSON с formData, section, type (needRequired|allowedNewStep).

### getUserPersonalMenu (GET /api/personal/getUserPersonalMenu/)

- **Логика:** Формирование пунктов меню личного кабинета (по ролям и настройкам).
- **Ответ:** JSON с меню.

### saveUserFields (POST /api/personal/saveUserFields/)

- **Вход:** данные полей профиля (formData, section и т.д.).
- **Логика:** Валидация через FormBuilderService, сохранение в user->data (или expansionData/requisitesData/estateData в зависимости от секции).
- **Ответ:** JSON success/error.

### saveUserImg (POST /api/personal/saveUserImg/)

- Аналогично FormController::saveUserImg, но для уже зарегистрированного пользователя (личный кабинет).

### setUserEmail, checkEmailCode (POST)

- Смена/подтверждение email в личном кабинете (отправка кода, проверка, обновление user->email).

### changeUserPhone, confirmChangeUserPhone (POST)

- Запрос смены телефона (отправка SMS) и подтверждение по коду.

### getRequisitesData, getEstateData (GET)

- Возврат данных из user->requisitesData и user->estateData.

### saveRequisitesData, saveEstateData (POST)

- Сохранение реквизитов и недвижимости в соответствующие JSON-поля пользователя.

### deleteRequisite, deleteEstate (POST)

- Удаление элемента из массивов requisitesData/estateData.

### getFormActivities, saveUserFieldsActivities (GET/POST)

- Получение/сохранение полей активностей в профиле (связь с directory_view_activities и данными пользователя).

### getBic (GET /api/personal/getBic/)

- Поиск БИК по номеру счёта (справочник банков или внешний сервис).

### getMapField, setMapField (GET/POST)

- Получение/установка mapAddress, mapRadius, latitude, longitude пользователя.

---

## 5. PersonalArea\DocumentsController

Все методы под prefix /api/personal/documents/, auth + scope personalArea.

### getDocumentSigned, getDocumentArchive, getDocumentInquiries, getDocumentConclude, getDocumentTerminate (GET)

- Выборка документов пользователя по статусу/типу (подписанные, архив, запросы, на заключение, на расторжение). Возврат списка с метаданными.

### setConclude (POST)

- Отправка запроса на заключение договора (интеграция с 1C/Nopaper по сценарию проекта).

### setTerminate (POST)

- Запрос расторжения договора.

### getCompanyAndCertificatesInquiries (GET)

- Данные компании и сертификатов для запросов справок.

### requestInquiries (POST)

- Запрос справок (1C/внешний сервис).

### createDocument (POST createTestDoc)

- Создание тестового/основного документа для пользователя (генерация PDF, отправка на подпись и т.д.).

---

## 6. UserRoles\ClientController

Зависимость: `OrderRepository`. Все методы — по роутингу с CheckRole:client или через UniversalController.

### getBrand

- **Логика:** Проекты пользователя → все бренды из проектов (flatMap + unique). Возврат BrandResource::collection.
- **Ответ:** Коллекция брендов.

### setBrandImg(SetBrandImgRequest)

- **Вход:** brandId. Логика: поиск бренда среди проектов пользователя; если найден — user->img = logo бренда, save. SuccessResource.

### getPlace

- **Логика:** Места из проектов пользователя (project->places, unique). PlaceResource::collection.

### setPlace(SetPlaceRequest)

- **Вход:** placeId (массив). Синхронизация user->place()->sync($placeIds) только для мест, входящих в проекты пользователя. SuccessResource.

### delPlace(DelPlaceRequest)

- **Вход:** placeId. user->place()->detach($placeId). SuccessResource.

### setUserData(SetUserDataRequest)

- **Вход:** name. user->name = $request->name, save. SuccessResource.

### createOrder(CreateOrderRequest)

- **Логика:** orderRepository->createOrder($request, Auth::id()). Возврат OrderResource.

### repeatOrder(RepeatOrderRequest)

- **Логика:** orderRepository->repeatOrder($request). OrderResource.

### createOrderActivity, updateOrderActivity, deleteOrderActivity (Request)

- Делегирование в orderRepository (createOrderActivity, updateOrderActivity, deleteOrderActivity), возврат OrderResource.

### updateOrder(UpdateOrderRequest)

- orderRepository->updateOrder($request). OrderResource.

### getOrders(GetOrderRequest)

- orderRepository->getUserOrderByStatusPaginate($request->status, Auth::id()). OrderResource::collection.

### getOrder(GetOrderRequest)

- orderRepository->getUserOrderByStatus(Auth::id(), $request->orderId). OrderResource.

### cancelOrder(OrderByIdCancelRequest)

- orderRepository->cancelOrder($request->orderId). SuccessResource или ErrorResource.

### sendOrder(OrderByIdRequest)

- orderRepository->sendOrder($request->orderId). SuccessResource или ErrorResource.

### getViewActivitiesForOrder(GetViewActivitiesForOrderRequest)

- **Вход:** orderId. Загрузка заказа, места заказа → проекты места → viewActivities (unique). Если заказ не self_employed — фильтр по self_employed=false. ViewActivityResource::collection.

---

## 7. UserRoles\ManagerController

Зависимости: `UserRepository`, `OrderRepository`. Методы вызываются при роли manager (напрямую или через UniversalController).

- **getBrand, setBrandImg, getPlace, setPlace, delPlace, setUserData** — по смыслу как у Client, но для менеджера (проекты/места менеджера).
- **getCounterparty** — контрагенты текущего пользователя. CounterpartyResource::collection.
- **setCounterparty(SetCounterpartyRequest)** — привязка контрагентов к пользователю (syncWithoutDetaching). Проверка роли пользователя (userId из запроса) — manager/client/specialist.
- **deleteCounterparty** — detach контрагента, пересчёт привязок проектов и мест пользователя (удаление проектов/мест, не привязанных к оставшимся контрагентам).
- **getProject, setProject, delProject** — CRUD проектов пользователя (user_directory_project, directory_project).
- **getPlaceModeration, setPlaceModeration, delPlaceModeration** — места в контексте модерации (доступные менеджеру места).
- **getOrders, getOrder, acceptOrder** — через orderRepository: getOrderByUserSyncDataPaginate/getOrderByUserSyncData, acceptedOrder.
- **convertTask(ConvertTaskRequest)** — orderRepository->convertTask($user, $request). Создание задачи из заказа.
- **getSurepvisorData** — супервайзеры для задачи (связь manager_supervisor, пользователи).
- **createTask, updateTask** — orderRepository->createTask/updateTask.
- **createTaskActivity, updateTaskActivity, deleteTaskActivity** — активности задачи через репозиторий.
- **getViewActivitiesForTask** — виды активностей по месту/проекту задачи.
- **instructTask(InstructTaskRequest)** — orderRepository->instructTask(taskId, supervisorIds).
- **invoiceTask** — выставить счёт по задаче (orderRepository->invoiceTask).
- **cancelTask** — orderRepository->cancelTask.
- **getProjectForTask** — проекты для выбора по задаче.
- **repeatTask** — orderRepository->repeatTask.
- **getBids, getBid** — orderRepository->getBidsByUserSyncDataPaginate, getBidByUserSyncData.
- **invoiceBid, cancelBid** — orderRepository->invoiceBid, cancelBid.
- **acceptSpecialist, declinedSpecialist** — принятие/отклонение специалиста по ставке (accept_bid, обновление bid).
- **getSpecialistForBid** — список специалистов (accept_bid по bid_id).
- **updateBid** — orderRepository->updateBid.
- **getJobs, getJob** — работы (принятые ставки) через orderRepository->getJobsByUserSyncDataPaginate, getJobByUser.
- **endSpecialistJob** — завершение работы специалиста (orderRepository, смена статусов bid/report).
- **acceptReport, acceptAllReportJob** — приём отчёта/всех отчётов по работе.
- **payReport** — отметка оплаты отчёта (статус forPay → paid и т.д.).
- **updateReport** — обновление часов/коэффициента/причин отчёта.
- **getReasons** — справочник directory_reasons (ReasonsResource).
- **getModerationClient, getModerationSingleClient** — пользователи на модерации (userRepository->getModerationUsersPaginate, getModerationUser).
- **confirmUserRegister** — подтверждение регистрации (confirmRegister = true и т.д.).
- **setUserImg(SetUserImgRequest)** — загрузка фото пользователя (для модерации, userId в запросе).
- **getUserSurepvisorData, getSurepvisors, setSurepvisors, delSurepvisor** — супервайзеры пользователя (manager_supervisor, привязка к пользователю по id).

---

## 8. UserRoles\SupervisorController

Наследует/дублирует часть методов ManagerController (payReport, updateReport, getReasons, getJobs, getJob, endSpecialistJob и т.д.). Логика та же, но с проверкой роли supervisor и доступом к своим подчинённым специалистам/задачам.

---

## 9. UserRoles\RecruiterController

### getRequests, getRequest (GET)

- Список заявок (requests) и одна заявка по id. Фильтр по пользователю (recruiter). RequestResource.

### acceptRequest (POST)

- Принятие заявки: обновление status/accept_user_id в requests, создание связей со ставкой/задачей по бизнес-логике репозитория.

---

## 10. UserRoles\SpecialistController

### acceptBid (POST)

- orderRepository->acceptBid($user, $request->bidId). Специалист принимает ставку (запись в accept_bid, смена статуса bid).

### rejectBid (POST)

- orderRepository->rejectBid($user, $request->bidId). Отклонение ставки.

### startDay, endDay (POST)

- Отметка начала/окончания рабочего дня (привязка к bid/report, PVP при необходимости).

### endJob (POST)

- Завершение работы по ставке (orderRepository, статусы bid/report).

### payReport (POST payReportSpecialist)

- Специалист отмечает, что отчёт оплачен (статус отчёта paid).

### getCounterpartiesForSign (GET)

- Контрагенты, по которым специалисту нужно подписать документы.

### signContracts (POST)

- Инициация подписания договоров (Nopaper и т.д.).

### signedDocuments (POST/GET), signedDocumentsSendCode, signedDocumentsRetriesSms, getSignetDocument

- Работа с подписанием через Nopaper: создание черновика, отправка кода, повтор SMS, получение статуса/документа.

---

## 11. UniversalController

Конструктор: при auth загружает роли пользователя в `$this->roles`.

Методы — роутеры по роли:

- **getData** — UserForModerationResource(Auth::user()).
- **getBrand, setBrandImg** — если client → ClientController, если manager → ManagerController; иначе 403.
- **getPlace, setPlace, delPlace, setUserData** — client → ClientController, manager → ManagerController, recruiter → RecruiterController, supervisor → SupervisorController; иначе 403.
- **getOrders, getOrder, acceptOrder** — client → ClientController, manager → ManagerController (или общая логика по sync-данным).
- **getTasks, getTask, acceptTask** — менеджер/супервайзер → соответствующий контроллер.
- **createSearchFromOrder, createSearchFromTask, updateSearch** — делегирование в OrderRepository через контроллер менеджера.
- **getBids, getBid, invoiceBid, cancelBid, acceptSpecialist, declinedSpecialist, getSpecialistForBid, updateBid** — ManagerController/SupervisorController.
- **getPlaceForOrder, getPlaceForTask, getPlaceForBid, getRadiusSelect** — получение мест/радиусов для форм заказа/задачи/ставки (по проектам/местам пользователя).
- **createBidFromOrder, createBidFromTask** — OrderRepository через менеджера.
- **getJobs, getJob, endSpecialistJob** — Manager/Supervisor.
- **acceptReport, acceptAllReportJob, payReport, updateReport, getReasons** — Manager/Supervisor.
- **getManager, setManagers, delManager** — привязка менеджеров к пользователю (клиенту/специалисту).
- **moderation/** (getProject, setProject, delProject, getPlaceModeration, setPlaceModeration, delPlaceModeration, getModerationClient, getModerationSingleClient, confirmUserRegister, setUserImg, getUserSurepvisorData, getSurepvisors, setSurepvisors, delSurepvisor, getCounterparty, setCounterparty, deleteCounterparty) — делегирование в ManagerController или отдельные методы модерации (UserRepository, привязки проектов/мест/супервайзеров/контрагентов).

Все ответы — JSON (Resource или массив), при неверной роли — 403.

---

## 12. Settings\SettingsController (API)

### getFromKey (GET /api/settings/getFromKey/)

- **Вход:** query `key`. Auth:api.
- **Логика:** Setting::where('key', $request->key)->first(). Возврат value в result.
- **Ответ:** JSON `{ status: 'success', result: value }`.

### getAll (GET /api/settings/getAll/)

- **Логика:** Setting::all(). Массив { key, value } в result.
- **Ответ:** JSON `{ status: 'success', result: [...] }`.

---

## 13. Integration\IntegrationController

Middleware: CheckIntegration (проверка ключа/подписи интеграции).

### ping (GET /api/integration/ping/)

- **Ответ:** JSON `{ status: 'success' }`. Проверка доступности API интеграции.

### updateUserData (POST /api/integration/updateUserData/)

- **Вход:** `userId` (обязателен), `updateData` (объект ключ–значение), `errorData`, `expansionData`.
- **Логика:** Поиск User по id (404 если нет). Обновление: для каждого ключа из updateData — запись в user->data и удаление из user->updateData; errorData и expansionData мержатся с существующими JSON-полями. Сохранение пользователя.
- **Ответ:** JSON `{ status: 'success' }` или 404.

---

## 14. Admin-контроллеры (кратко)

- **Admin\Auth\LoginController:** customAdminLogin — проверка учётных данных админа, сессия; logout — выход.
- **Admin\Page\MainPageController:** mainPage — отображение главной админки.
- **Admin\Page\UsersController:** usersCreate/usersCreateAjax, usersList, userEdit/userEditAjax, userDelete — CRUD пользователей (роли, данные).
- **Admin\Page\Fields\FieldsController:** CRUD полей форм (fields, fields_user_role).
- **Admin\Page\Fields\Directory\*** (Country, Bank, Activities, …): для каждого справочника — create, createAjax, list, edit, editAjax, delete (работа с таблицей directory_*).
- **Admin\Import\ImportController:** index — форма импорта; import — разбор файла; importSave — сохранение в справочники.
- **Admin\Settings\SettingsController:** index — форма настроек; save — сохранение ключ–значение в settings.
- **Admin\Certificates\CertificatesController:** index, save — управление сертификатами.
- **Admin\QrCode\QrCodeController:** index, getBindings, createUserLink — QR и привязки пользователей.
- **Admin\ForTest\OrderController, TaskController, BidController:** list — список тестовых сущностей; delete — удаление по id.
- **Admin\Document\DocumentController:** documentTest/documentTestSave — тест генерации; checkDocument — скачивание/проверка; documentsCreate/createAjax, documentsList, documentsEdit/editAjax, documentsDelete — CRUD шаблонов и документов (document_templates, documents).

Все админ-методы требуют middleware CheckPermission (роль admin). Возвращают view (Blade) или JSON для Ajax-действий.
