<?php

namespace App\Services\PVP\Verme;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VermeService
{
    protected string $baseUrl;
    protected array $defaultAuth;

    public function __construct()
    {
        $this->baseUrl = config('services.timesheet.url');
        $this->defaultAuth = [
            'login' => config('services.timesheet.login'),
            'password' => config('services.timesheet.password')
        ];
    }

    static function sendUserInfo(User $user): bool
    {
        return true;
    }

    public function getTimesheets(array $filters = [])
    {
        $payload = [
            'getTimesheets' => array_merge([
                'headquarter' => ['code' => 'bnt'],
                'start_date' => '2025-04-01',
                'end_date' => '2025-04-20',
                'authData' => $this->defaultAuth
            ], $filters)
        ];

        return $this->sendRequest($payload);
    }

    public function createEmployee(array $employeeData)
    {
        $payload = [
            'setAgencyEmployee' => array_merge([
                'headquarter' => ['code' => 'bnt'],
                'disableJobHistory' => false,
                'disableAgencyHistory' => false,
                'disableOrgHistory' => true,
                'disableEmployeeEvent' => true,
                'useBaseJobs' => true,
                'authData' => $this->defaultAuth
            ], $employeeData)
        ];

        return $this->sendRequest($payload, 'POST');
    }

    public function getShifts(array $filters = [])
    {
        $payload = [
            'getOutsourcingShiftsVer2' => array_merge([
                'timestamp' => now()->toIso8601String(),
                'amount' => 500,
                'headquarter' => ['code' => 'bnt'],
                'agency' => ['code' => 'bnt_agency_msk'],
                'authData' => $this->defaultAuth
            ], $filters)
        ];

        return $this->sendRequest($payload, 'POST');
    }

    public function assignToShift(array $shiftData)
    {
        $payload = [
            'setOutsourcingShift' => array_merge([
                'headquarter' => ['code' => 'bnt'],
                'agency' => [
                    'code' => 'bnt_agency_msk',
                    'headquarter' => ['code' => 'bnt']
                ],
                'authData' => $this->defaultAuth
            ], $shiftData)
        ];

        return $this->sendRequest($payload, 'POST');
    }

    protected function sendRequest(array $payload, string $method = 'POST')
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, $payload);

            if (!$response->successful()) {
                Log::error('API Request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payload' => $payload
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('API Request exception', [
                'message' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['error' => $e->getMessage()];
        }
    }
}
