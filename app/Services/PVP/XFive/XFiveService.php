<?php

namespace App\Services\PVP\XFive;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class XFiveService
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
    }

    /**
     * Получение access token
     */
    private function getAccessToken(): string
    {
        return Cache::remember('wop_access_token', 3500, function () {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (!$response->successful()) {
                return '';
            }

            return $response->json()['access_token'];
        });
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
        ])->{$method}($this->baseUrl . $endpoint, $data);

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
    public function getTask(int $taskId): array
    {
        return $this->makeRequest('get', '/task/v4', [
            'taskid' => $taskId
        ]);
    }

    /**
     * Реакция на задание
     */
    public function updateTaskSupplierStatus(int $taskId, int $status): array
    {
        return $this->makeRequest('post', '/task/supplier/update/v1', [
            'taskid' => $taskId,
            'statpvp' => $status
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
}
