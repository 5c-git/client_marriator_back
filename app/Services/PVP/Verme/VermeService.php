<?php

namespace App\Services\PVP\Verme;

use App\Enum\Document\DocumentTypeEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\User;
use App\Services\PVP\PVPAbstract;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VermeService  extends PVPAbstract
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
        parent::__construct();
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
            echo "<pre>";
            var_dump($response->status());
            echo "</pre>";

            echo "<pre>";
            var_dump($response->body());
            echo "</pre>";

            return $response->json();
        } catch (\Exception $e) {
            Log::error('API Request exception', [
                'message' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    public function registerUser(User $user)
    {
        $document = RecognitionDocument::query()
            ->where('user_id',$user->id)
            ->where('file_type',DocumentTypeEnum::Passport->value)
            ->orderBy('id','desc')
            ->first();
        /** @var RecognitionDocument $document */

        if ($document) {
//            if(!empty($document->data['Sex']))
//            {
//                if($document->data['Sex'] == 'МУЖ') {
//                    $sex = 1;
//                }else{
//                    $sex = 2;
//                }
//            }
//            $payload = [
//                'userPhone'           => $user->phone,
//                'email'               => $user->email,
//                'name'                => $document->data['FirstName']??'',
//                'surname'             => $document->data['LastName']??'',
//                'patronymic'          => $document->data['MiddleName'] ?? null, // Optional field
//                'isShortTimePassword' => true,
//                'birthDate'           => $document->data['BirthDate'] ? Carbon::parse($document->data['BirthDate'])->format('Y-m-d') :'',
//                'gender'              => $sex ?? null,
//                'passportData'        => [
//                    'series'               => $document->data['Series']??'',
//                    'number'               => $document->data['Number']??'',
//                    'issuedBy'             => $document->data['GivenBy']??'',
//                    'issuingDate'          => $document->data['GivenDate'] ? Carbon::parse($document->data['GivenDate'])->format('Y-m-d') :'',
//                    'issuerDepartmentCode' => $document->data['SubdivisionCode']??'',
//                    'birthPlace'           => $document->data['BirthPlace']??'',
//                ]
//            ];
            $sex = 'male';
            if(!empty($document->data['Sex']))
            {
                if($document->data['Sex'] == 'МУЖ') {
                    $sex = 'male';
                }else{
                    $sex = 'female';
                }
            }

            $employeeData = [
                'number' => 'test00000124',
                'employee' => [
                    'firstname' => $document->data['FirstName']??'',
                    'surname' => $document->data['LastName']??'',
                    'patronymic' => $document->data['MiddleName'] ?? null,
                    'gender' => $sex,
                    'dateOfBirth' => $document->data['BirthDate'] ? Carbon::parse($document->data['BirthDate'])->format('Y-m-d') :'',
                    'placeOfBirth' => $document->data['BirthPlace']??'',
                    'agency' => [
                        'code' => 'bnt_agency_msk',
                        'headquarter' => ['code' => 'bnt']
                    ],
                    "recieptDate"=> $user->created_at->format('Y-m-d'),
                ]
            ];

        } else {
            return false;
        }

        $data = $this->createEmployee($employeeData);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }
}
