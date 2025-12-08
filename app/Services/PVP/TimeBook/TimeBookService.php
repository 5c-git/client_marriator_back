<?php

namespace App\Services\PVP\TimeBook;

use App\Services\PVP\PVPAbstract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TimeBookService extends PVPAbstract
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.timeBook.base_url');
        if(Redis::exists('services.timeBook.access_token')){
            $this->token = Redis::get('services.timeBook.access_token');
        }else{
            $this->authenticate();
        }
        parent::__construct();
    }

    /**
     * Аутентификация в API
     */
    public function authenticate(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/auth", [
                'login' => config('services.timeBook.login'),
                'password' => config('services.timeBook.password'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['access_token'];
                $this->updateTokenInConfig($this->token);

                return true;
            }

            Log::error('Timebook auth failed', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Timebook auth error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Создание организации
     */
    public function createOrganization(array $dto): ?string
    {
        $response = $this->request('post', '/organizations', [
            'guid' => $dto['guid'],
            'name' => $dto['name'],
            'serial_number' => $dto['serialNumber'],
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание должности
     */
    public function createStaffPosition(array $dto): ?string
    {
        $response = $this->request('post', '/staff-positions/', [
            'guid' => $dto->guid,
            'name' => $dto->name,
            'serial_number' => $dto->serialNumber,
            'organization_guid' => $dto->organizationGuid,
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание объекта
     */
    public function createSubdivision(string $guid, string $name, string $serialNumber, string $organizationGuid): ?string
    {
        $response = $this->request('post', '/subdivisions/', [
            'guid' => $guid,
            'name' => $name,
            'serial_number' => $serialNumber,
            'organization_guid' => $organizationGuid,
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание сотрудника
     */
    public function createEmployee(EmployeeDto $dto): ?string
    {
        $response = $this->request('post', '/employees', [
            'guid' => $dto->guid,
            'staff_position_guid' => $dto->staffPositionGuid,
            'organization_guid' => $dto->organizationGuid,
            'subdivision_guid' => $dto->subdivisionGuid,
            'natural_person' => [
                'last_name' => $dto->lastName,
                'first_name' => $dto->firstName,
                'second_name' => $dto->secondName,
                'phone_number' => $dto->phoneNumber,
            ],
            'sex' => $dto->sex,
            'personnel_number' => $dto->personnelNumber,
            'hiring_date' => $dto->hiringDate,
            'deleted' => $dto->deleted,
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Получение смен
     */
    public function getDemands(array $params): ?array
    {
        return $this->request('post', '/spec/demands/', $params);
    }

    /**
     * Назначение на смену
     */
    public function assignToDemand(string $demandKey, string $employeeGuid): bool
    {
        $response = $this->request('post', '/demand_actions', [
            [
                'demand_key' => $demandKey,
                'actions' => [
                    [
                        'action' => 'set-performer',
                        'data' => [
                            'performer_guid' => $employeeGuid,
                        ],
                        'comment' => null,
                    ],
                ],
            ],
        ]);

        return $response !== null;
    }

    /**
     * Отмена назначения на смену
     */
    public function cancelAssignment(string $demandKey): bool
    {
        $response = $this->request('post', '/demand_actions', [
            [
                'demand_key' => $demandKey,
                'actions' => [
                    [
                        'action' => 'reset',
                        'comment' => null,
                    ],
                ],
            ],
        ]);

        return $response !== null;
    }

    /**
     * Подписка на вебхуки
     */
    public function createWebhookSubscription(array $data): ?string
    {
        $response = $this->request('post', '/webhook-subscription', $data);
        return $response ? $response['guid'] : null;
    }

    /**
     * Общий метод для запросов
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        echo "<pre>";
        var_dump($this->token);
        echo "</pre>";
        try {
            $response = Http::withHeaders([
                'X-Access-Token' => $this->token,
            ])->$method("{$this->baseUrl}{$endpoint}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 401) {
                if ($this->authenticate()) {
                    $response = Http::withHeaders([
                        'X-Access-Token' => $this->token,
                    ])->$method("{$this->baseUrl}{$endpoint}", $data);

                    if ($response->successful()) {
                        return $response->json();
                    }
                }
            }
            echo "<pre>";
            var_dump($response->body());
            echo "</pre>";

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Обновление токена в конфигурации
     */
    private function updateTokenInConfig(string $token): void
    {
        Redis::set('services.timeBook.access_token',$token);
    }
}
