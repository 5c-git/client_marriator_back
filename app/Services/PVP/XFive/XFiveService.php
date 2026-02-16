<?php

namespace App\Services\PVP\XFive;

use App\Enum\Document\DocumentTypeEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Bid;
use App\Models\User;
use App\Services\PVP\PVPAbstract;
use App\Services\User\UserDataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class XFiveService  extends PVPAbstract
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $tokenUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.xFive.base_url');
        $this->clientId = config('services.xFive.client_id');
        $this->clientSecret = config('services.xFive.client_secret');
        $this->tokenUrl = config('services.xFive.token_url');
        parent::__construct();
    }

    /**
     * Получение access token
     */
    private function getAccessToken(): string
    {
        //return Cache::remember('wop_access_token', 3500, function () {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (!$response->successful()) {
                return '';
            }

            return $response->json()['access_token'];
        //});
    }

    /**
     * Базовый метод для выполнения запросов
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])
            ->{$method}($this->baseUrl . $endpoint,$data);
        if (!$response->successful()) {
            return [];
        }

        return $response->json();
    }

    /**
     * АКТЫ
     */

    /**
     * Получить акты за период (v2 - актуальная версия)
     */
    public function getActs(int $month, int $year): array
    {
        return $this->makeRequest('get', '/acts/v2', [
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Подтверждение проекта акта (v2 - актуальная версия)
     */
    public function confirmAct(array $bnkfy, array $vbeln): array
    {
        return $this->makeRequest('post', '/act/confirm/v2', [
            'bnkfy' => $bnkfy,
            'vbeln' => $vbeln
        ]);
    }

    /**
     * ЗАДАНИЯ
     */

    /**
     * Получить задания за день (v4 - актуальная версия)
     */
    public function getTasks(int $day, int $month, int $year): array
    {
        return $this->makeRequest('get', '/tasks/v4', [
            'day' => $day,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Получить задание по ID (v4 - актуальная версия)
     */
    public function getTimesheets(User $user, Bid $bid): ?float
    {
        $data = $this->makeRequest('get', '/task/v4', [
            'taskid' => $bid->external_id
        ]);
        if(!empty($data) && !empty($data["task"]) && !empty($data["task"][0]) && !empty($data["task"][0]["facthrs"])){
            return (float)$data["task"][0]["facthrs"];
        }
        return null;
    }

    /**
     * Реакция на задание
     */
    public function updateTaskSupplierStatus(int $taskId): array
    {
        return $this->makeRequest('post', '/task/supplier/update/v1', [
            'taskid' => $taskId,
            'statpvp' => 1
        ]);
    }

    /**
     * Изменение статуса задания
     */
    public function updateTaskStatus(int $taskId, ?int $status = null, ?string $comment = null): array
    {
        $data = ['taskid' => $taskId];
        if ($status !== null) $data['statu'] = $status;
        if ($comment !== null) $data['commnt'] = $comment;

        return $this->makeRequest('post', '/task/update/v1', $data);
    }

    /**
     * ПЕРСОНАЛ
     */

    /**
     * Поиск работника по параметрам (v2 - актуальная версия)
     */
    public function findStaff(?string $inn = null, ?string $pervp = null, ?string $snils = null): array
    {
        $params = array_filter([
            'inn' => $inn,
            'pervp' => $pervp,
            'snils' => $snils
        ]);

        return $this->makeRequest('get', '/staff/find/v2', $params);
    }

    /**
     * Создание работника
     */
    public function createStaff(array $staffData): array
    {
        return $this->makeRequest('post', '/staff/create/v1', $staffData);
    }

    /**
     * Обновление работника
     */
    public function updateStaff(array $staffData): array
    {
        return $this->makeRequest('post', '/staff/update/v1', $staffData);
    }

    /**
     * Получение информации о работнике
     */
    public function getStaffInfo(?string $pervp = null): array
    {
        return $this->makeRequest('get', '/staff/info/v1', array_filter([
            'pervp' => $pervp
        ]));
    }

    /**
     * ОБЪЕКТЫ (ОРГАНИЗАЦИОННЫЕ ЕДИНИЦЫ)
     */

    /**
     * Справочник орг. единиц (v4 - актуальная версия)
     */
    public function getOrgehList(): array
    {
        return $this->makeRequest('get', '/orgeh/list/v4');
    }

    /**
     * Информация об организационной единице
     */
    public function getOrgeh(int $orgeh): array
    {
        return $this->makeRequest('get', '/orgeh/v1', [
            'orgeh' => $orgeh
        ]);
    }

    /**
     * Справочник юридических лиц заказчика
     */
    public function getBukrsList(): array
    {
        return $this->makeRequest('get', '/orgeh/bukrs/list/v1');
    }

    /**
     * ЗАЯВКИ
     */

    /**
     * Информация о заявках за период
     */
    public function getPurchases(int $month, int $year): array
    {
        return $this->makeRequest('get', '/purchases/v1', [
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Реакция на заявку
     */
    public function updatePurchase(int $idprec, int $status): array
    {
        return $this->makeRequest('post', '/purchase/update/v1', [
            'idprec' => $idprec,
            'prstat' => $status
        ]);
    }

    /**
     * ИЗМЕНЕНИЯ
     */

    /**
     * Запрос изменений
     */
    public function getChanges(?int $last = 0, ?int $limit = 100, ?int $polling = 5): array
    {
        return $this->makeRequest('get', '/changes/v1', array_filter([
            'last' => $last,
            'limit' => $limit,
            'polling' => $polling
        ]));
    }

    /**
     * ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ
     */

    /**
     * Справочник статусов заданий
     */
    public function getTaskStatuses(): array
    {
        return $this->makeRequest('get', '/task/statuses/v1');
    }

    /**
     * Справочник статусов поставщика на задании
     */
    public function getTaskSupplierStatuses(): array
    {
        return $this->makeRequest('get', '/task/supplier/statuses/v1');
    }

    /**
     * Справочник статусов заявок
     */
    public function getPurchaseStatuses(): array
    {
        return $this->makeRequest('get', '/purchase/statuses/v1');
    }

    /**
     * Справочник должностей работников (v2 - актуальная версия)
     */
    public function getStells(): array
    {
        return $this->makeRequest('get', '/staff/stells/v2');
    }

    /**
     * Изменение работника на задании
     */
    public function assignToShift(User $user,string $guid): ?bool
    {
        $dataUser = $this->findStaff(null,$user->id,null);
        if(empty($dataUser['extid'])){
            $this->registerUser($user);
            $dataUser = $this->findStaff(null,$user->id,null);
        }
        if(!empty($dataUser) && !empty($dataUser['extid'])) {
            $res = $this->updateTaskSupplierStatus($guid);
            sleep(10);
            $data = [
                "extid"  => $dataUser['extid'],
                "taskid" => $guid,
            ];
            $data = $this->makeRequest('post', '/task/staff/v1', $data);
            if(!empty($data['action']) && !empty($data['action']["id"])){
                return true;
            }else{
                return null;
            }
        } else{
            return null;
        }
    }

    /**
     * Отклик работника на задании
     */
    public function assignToShiftK(User $user,string $guid): ?array
    {
        $dataUser = $this->findStaff(null,$user->id,null);
        if(empty($dataUser['extid'])){
            $this->registerUser($user);
            $dataUser = $this->findStaff(null,$user->id,null);
        }
        if(!empty($dataUser) && !empty($dataUser['extid'])) {
            $data = [
                "extid"  => $dataUser['extid'],
                "taskid" => $guid,
                "estat"  => 1
            ];
            return $this->makeRequest('post', '/task/staff/feedback/v1', $data);
        } else{
            return null;
        }
    }

    /**
     * Изменение оценки магазина на задании поставщиком
     */
    public function updateTaskPvpScore(array $data): array
    {
        return $this->makeRequest('post', '/task/pvpscore/v1', $data);
    }

    /**
     * Прикрепление/удаление чека с задания
     */
    public function updateTaskSmzPay(array $data): array
    {
        return $this->makeRequest('post', '/task/pay/update/v1', $data);
    }

    /**
     * Получить статусы откликов работника
     */
    public function getTaskStaffStatuses(): array
    {
        return $this->makeRequest('get', '/task/staff/statuses/v1');
    }

    /**
     * Получить причины победы/проигрыша в конкурсе
     */
    public function getTaskReasons(): array
    {
        return $this->makeRequest('get', '/task/reasons/v1');
    }

    /**
     * Получить причины победы/проигрыша в конкурсе
     */
    public function getSectorList(): array
    {
        return $this->makeRequest('get', '/sector/list/v1');
    }

    public function registerUser(User $user)
    {
//        $dataSectors = $this->getSectorList();
        $document = RecognitionDocument::query()
            ->where('user_id',$user->id)
            ->where('file_type',DocumentTypeEnum::Passport->value)
            ->orderBy('id','desc')
            ->first();
        /** @var RecognitionDocument $document */
        $snils = UserDataService::getUserSnils($user);

        if ($document && !empty($snils)) {
            if(!empty($document->data['Sex']))
            {
                if($document->data['Sex'] == 'МУЖ') {
                    $sex = 1;
                }else{
                    $sex = 2;
                }
            }

            $payload = [
                'gender' => (string)($sex ?? 1),
                'name1'  => $document->data['LastName'] ?? '',
                'name2'  => $document->data['FirstName'] ?? '',
                'mob2'   => (string)$user->phone,
                'pervp'  => (string)$user->id,
                'secid'  => 54258840,
                'snils'=>$snils,
            ];
        } else {
            return false;
        }

        if(!empty($payload)){
            $dataRegister = $this->createStaff($payload);
            if(!empty($dataRegister['action']["id"])){
                return true;
            }
        }
        return false;
    }

    public function getData(): array
    {
        return $this->dataFormater($this->getTasks(3, 9, 2025));
    }

    protected function dataFormater($data): array
    {
        $returnArray = [];
        if(!empty($data['task'])){
            foreach ($data['task'] as $dataShift) {
                if (in_array($dataShift['statu'],[7,8,14,15,16,19])) {
                    $array                 = [];
                    $array['place']        = $dataShift['orgeh'];
                    $array['selfEmployed'] = true;
                    $addDay = false;
                    if($dataShift['begtm']>$dataShift['endtm']){
                        $addDay = true;
                    }
                    if(strlen((string)$dataShift['begtm']) <= 5){
                        $dataShift['begtm'] = '0'.$dataShift['begtm'];
                    }
                    if(strlen((string)$dataShift['endtm']) <= 5){
                        $dataShift['endtm'] = '0'.$dataShift['endtm'];
                    }
                    if(strlen((string)$dataShift['begtm'])<6){
                        $dataShift['begtm'] = str_pad($dataShift['begtm'], 6, '0', STR_PAD_LEFT);
                    }
                    if(strlen((string)$dataShift['endtm'])<6){
                        $dataShift['endtm'] = str_pad($dataShift['endtm'], 6, '0', STR_PAD_LEFT);
                    }

                    $array['dateStart']    = Carbon::parse($dataShift['dttask'].$dataShift['begtm']);
                    if($addDay) {
                        $array['end'] = Carbon::parse($dataShift['dttask'] . $dataShift['endtm'])->addDay();
                    }else{
                        $array['end'] = Carbon::parse($dataShift['dttask'] . $dataShift['endtm']);
                    }
                    $array['externalId']   = $dataShift['taskid'];
                    $array['job']          = $dataShift['stell'];
                    $returnArray[]         = $array;
                }
            }
        }
        return $returnArray;
    }

    public function getPrefix():string
    {
        return 'x_';
    }

    public function getType():int
    {
        return 2;
    }
}
