<?php

namespace App\Services\PVP\TimeBook;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimeBookService
{
    private string $baseUrl;
    private string $token;
    private bool $isAuthenticated = false;

    public function __construct()
    {
        $this->baseUrl = config('timebook.base_url');
        $this->token = config('timebook.access_token');
    }

    /**
     * Аутентификация в API
     */
    public function authenticate(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/auth", [
                'login' => config('timebook.login'),
                'password' => config('timebook.password'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['access_token'];
                $this->isAuthenticated = true;

                // Сохраняем токен в .env или кэш
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
    public function createOrganization(OrganizationDto $dto): ?string
    {
        $response = $this->request('post', '/organizations/', [
            'guid' => $dto->guid,
            'name' => $dto->name,
            'serial_number' => $dto->serialNumber,
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание должности
     */
    public function createStaffPosition(StaffPositionDto $dto): ?string
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
        if (!$this->isAuthenticated && !$this->authenticate()) {
            throw new \Exception('Failed to authenticate with Timebook API');
        }

        try {
            $response = Http::withHeaders([
                'X-Access-Token' => $this->token,
            ])->$method("{$this->baseUrl}{$endpoint}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            // Если токен протух, пробуем аутентифицироваться заново
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

            Log::error('Timebook API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Timebook API request error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Обновление токена в конфигурации
     */
    private function updateTokenInConfig(string $token): void
    {
        // Здесь можно реализовать сохранение токена в БД или кэш
        // Для примера сохраняем в файл .env
        $path = base_path('.env');
        $env = file_get_contents($path);

        if (str_contains($env, 'TIMEBOOK_ACCESS_TOKEN')) {
            $env = preg_replace(
                '/TIMEBOOK_ACCESS_TOKEN=.*/',
                "TIMEBOOK_ACCESS_TOKEN={$token}",
                $env
            );
        } else {
            $env .= "\nTIMEBOOK_ACCESS_TOKEN={$token}";
        }

        file_put_contents($path, $env);
    }
}
