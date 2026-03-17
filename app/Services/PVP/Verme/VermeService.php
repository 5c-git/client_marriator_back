<?php

namespace App\Services\PVP\Verme;

use App\Enum\Document\DocumentTypeEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Bid;
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

    public function getTimesheets(User $user, Bid $bid):?float
    {
        $payload = [
            'getTimesheets' => [
                'headquarter'     => ['code' => 'bnt'],
                'start_date'      => $bid->date_start->format('Y-m-d'),
                'end_date'        => $bid->date_end->format('Y-m-d'),
                "agency_employee" => [
                    "number" => (string)$user->id
                ],
                'authData'        => $this->defaultAuth
            ]
        ];

        return $this->getResultData($this->sendRequest($payload));
    }

    public function getResultData(?array $data):?float
    {
        $hours = 0;
        if(!empty($data) && !empty($data[0])){
            if(!empty($data[0]['dvalue'])){
                $hours+=$data[0]['dvalue'];
            }
            if(!empty($data[0]['nvalue'])){
                $hours+=$data[0]['nvalue'];
            }
        }
        return $hours?round($hours,3):null;
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
                'timestamp' => now()->subDay(),
                'amount' => 100,
                'headquarter' => ['code' => 'bnt'],
                //'agency' => ['code' => 'bnt_agency_msk'],
                'authData' => $this->defaultAuth
            ], $filters)
        ];
        return $this->sendRequest($payload, 'POST');
    }

    public function assignToShift(User $user,string $guid)
    {
        //$this->registerUser($user);
        $payload = [
            'setOutsourcingShift' => [
                'headquarter' => ['code' => 'bnt'],
                'agency' => [
                    'code' => 'bnt_agency_msk',
                    'headquarter' => ['code' => 'bnt']
                ],
                "guid"        => $guid,
                "employee"    => [
                    "agency_number" => (string)$user->id
                ],
                'authData'    => $this->defaultAuth
            ]
        ];

        $data = $this->sendRequest($payload, 'POST');

        if(!empty($data)){
            return true;
        }
        return null;
    }

    protected function sendRequest(array $payload, string $method = 'POST')
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(3000)->post($this->baseUrl, $payload);

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

    public function registerUser(User $user)
    {
        $document = RecognitionDocument::query()
            ->where('user_id',$user->id)
            ->where('file_type',DocumentTypeEnum::Passport->value)
            ->orderBy('id','desc')
            ->first();
        /** @var RecognitionDocument $document */

        if ($document) {
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
                'headquarter' => [
                    'code' => 'bnt'
                ],
                'number' => (string)$user->id,
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
                ],
                "useBaseJobs"=> true,
                //"jobList": [
                //            {
                //                "job": {
                //                    "code": "brigada_base"
                //                },
                //                "start": "2025-11-04",
                //                "end": "2026-11-04"
                //            }
                //        ],
            ];

        } else {
            return false;
        }

        return $this->createEmployee($employeeData);
    }

    public function getData(): array
    {
        return $this->dataFormater($this->getShifts());
    }

    protected function dataFormater($data): array
    {
        $returnArray = [];
        if(!empty($data['shifts_list'])){
            foreach ($data['shifts_list'] as $dataShift) {

                    $array                 = [];
                    $array['place']        = $dataShift['organization']['code'];
                    $array['selfEmployed'] = true;
                    $array['dateStart']    = Carbon::parse($dataShift['start']);
                    $array['end']          = Carbon::parse($dataShift['end']);
                    $array['externalId']   = $dataShift['guid'];
                    $array['job']          = $dataShift['job']['code'];
                    $returnArray[]         = $array;

            }
        }
        return $returnArray;
    }

    public function getPrefix():string
    {
        return 'v_';
    }

    public function getType():int
    {
        return 1;
    }
}
