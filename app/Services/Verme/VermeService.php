<?php

namespace App\Services\Verme;

use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Report;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VermeService
{
    private string $baseUrl;
    private string $login;
    private string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.timesheet.url');
        $this->login = config('services.timesheet.login');
        $this->password = config('services.timesheet.password');
    }

    static function sendUserInfo(User $user): bool
    {
        return true;
    }

    public function getTimesheets()
    {
        try {

            $requestData = $this->buildRequestData();

            $response = Http::timeout(30)
                ->retry(3, 100)
                ->acceptJson()
                ->contentType('application/json')
                ->post($this->baseUrl, $requestData);

            echo "<pre>";
            var_dump($response->body());
            echo "</pre>";

            if ($response->failed()) {
                throw new \Exception('API request failed: ' . $response->status());
            }

            $responseData = $response->json();
            Log::info('Timesheet API Response', ['data' => $responseData]);

            return $responseData;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function buildRequestData($dto = null): array
    {
        $data = [
            'getTimesheets' => [
                'headquarter' => [
                    'code' => 'bnt'
                ],
                'start_date' => '2023-01-16',
                'end_date' => '2023-01-16',
                'authData' => [
                    'login' => $this->login,
                    'password' => $this->password
                ],
                'agency'=> [
                    'code' => '002',
                    'headquarter' => [
                        'code' => 'bnt'
                    ]
                ]
            ]
        ];



//        if ($dto->organizationCode || $dto->organizationAltCode) {
//            $organization = [];
//
//            if ($dto->organizationCode) {
//                $organization['code'] = $dto->organizationCode;
//            }
//
//            if ($dto->organizationAltCode) {
//                $organization['alt_code'] = $dto->organizationAltCode;
//            }
//
//            $organization['headquarter'] = [
//                'code' => $dto->isTest ? 'auchan-test' : 'auchan'
//            ];
//
//            $data['getTimesheets']['organization'] = $organization;
//        }
//
//        if ($dto->agencyEmployeeNumber) {
//            $data['getTimesheets']['agency_employee'] = [
//                'number' => $dto->agencyEmployeeNumber
//            ];
//        }

        return $data;
    }

    public static function updateReportStat(Report $report): void
    {
        $report->coefficient = rand(1,100)/100;
        $report->status = ReportStatusEnum::reported->value;
        $report->save();
    }

}
