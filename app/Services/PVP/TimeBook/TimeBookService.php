<?php

namespace App\Services\PVP\TimeBook;

use App\Enum\Document\DocumentTypeEnum;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Bid;
use App\Models\User;
use App\Services\PVP\PVPAbstract;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TimeBookService extends PVPAbstract
{
    private string $baseUrl;
    private string $token;

    private string $organization = '550e8400-e29b-41d4-a716-441655440004';
    private string $subdivision = '550e8400-e29b-41d4-a716-441655440005';
    private string $position = '550e8400-e29b-41d4-a716-441655440006';

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
        $response = $this->request('post', '/staff-positions', [
            'guid' => $dto['guid'],
            'name' => $dto['name'],
            'serial_number' => $dto['serialNumber'],
            'organization_guid' => $dto['organizationGuid'],
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание объекта
     */
    public function createSubdivision(array $dto): ?string
    {
        $response = $this->request('post', '/subdivisions', [
            'guid' => $dto['guid'],
            'name' => $dto['name'],
            'serial_number' => $dto['serialNumber'],
            'organization_guid' => $dto['organizationGuid'],
        ]);

        return $response ? $response['guid'] : null;
    }

    /**
     * Создание сотрудника
     */
    public function createEmployee(User $user): ?string
    {
        $document = RecognitionDocument::query()
            ->where('user_id',$user->id)
            ->where('file_type',DocumentTypeEnum::Passport->value)
            ->orderBy('id','desc')
            ->first();

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

        } else {
            return false;
        }

        $uuid = Str::uuid()->toString();
        $response = $this->request('post', '/employees', [
            'guid' => $uuid,
            'staff_position_guid' => $this->position,
            'organization_guid' => $this->organization,
            'subdivision_guid' => $this->subdivision,
            'natural_person' => [
                'last_name' => $document->data['LastName']??'',
                'first_name' => $document->data['FirstName']??'',
                'second_name' => $document->data['MiddleName']??'',
                'phone_number' => (string)$user->phone,
            ],
            'sex' => $sex,
            'personnel_number' => (string)$user->id,
            'hiring_date' => $user->created_at->format('Y-m-d'),
            'deleted' => false,
        ]);

        if(!empty($response['guid'])){
            $user->time_book_guid = $uuid;
            $user->save();
            return true;
        }
        return false;
    }

    /**
     * Получение смен
     */
    public function getDemands($i = 0): ?array
    {
        $dataRes = [];
        $params = [
            'limit' => (50 + (50*$i)),
            'offset' =>(50*$i),
            'dateBegin' => Carbon::now()->addDay()->format('Y-m-d'),
            'dateEnd' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'statuses' => ['canceled','requested', 'new', 'assigned', 'wait4answer','deleted'],
        ];

        $data    = $this->request('post', '/demands', $params);

        if (!empty($data['demands'])) {
            $dataRes = $data['demands'];
            if (count($data['demands']) >= 50) {
                $dataRes = array_merge($dataRes, $this->getDemands($i+1));
            }
        }

        return $dataRes;
    }

    public function getTimesheets(User $user, Bid $bid):?float
    {
        $dataRes = [];
        $params = [
            'demandKeys'=> [$bid->external_id],
            //'limit' => 1000,
            //'offset' =>0,
            //'dateBegin' => Carbon::now()->addDay()->format('Y-m-d'),
            //'dateEnd' => Carbon::now()->addDays(10)->format('Y-m-d'),
            //'statuses' => ['canceled','requested', 'new', 'assigned', 'wait4answer','deleted'],
        ];
        $data    = $this->request('post', '/demands', $params);
        if(!empty($data) && !empty($data["demands"]) && !empty($data["demands"][0]) && !empty($data["demands"][0]["factTime"])){
            return round((float)($data["demands"][0]["factTime"]/(60*60)),3);
        }
        return null;
    }

    /**
     * Назначение на смену
     */
    public function assignToShift(string $demandKey, User $user): ?bool
    {
        if(!$user->time_book_guid) {
            $this->createEmployee($user);
        }

        $response = $this->request('post', '/demand-actions', [
            [
                'demand_key' => $demandKey,
                'actions' => [
                    [
                        'action' => 'set-performer',
                        'data' => [
                            'performer_guid' => $user->time_book_guid,
                        ],
                        'comment' => null,
                    ],
                ],
            ],
        ]);

        if(!empty($response) && !empty($response[0]) && isset($response[0]['success']) && $response[0]['success'] === true){
            return true;
        }

        return null;
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

    public function getData(): array
    {
        return $this->dataFormater($this->getDemands());
    }

    protected function dataFormater($data): array
    {
        $returnArray = [];
        foreach ($data as $dataShift) {
            if (in_array($dataShift['status'],['requested','new'])) {
                $array                 = [];
                $array['place']        = $dataShift['unitUuid'];
                $array['selfEmployed'] = true;
                $array['dateStart']    = Carbon::parse($dataShift['datetimeBegin']);
                $array['end']          = Carbon::parse($dataShift['datetimeEnd']);
                $array['externalId']   = $dataShift['demandKey'];
                $array['job']          = $dataShift['specialityUuid'];
                $returnArray[]         = $array;
            }
        }
        return $returnArray;

        //{
        //  "demands": [
        //    {
        //      "demandKey": "b57a076a-fd5f-ca1c-28e5-14134sd13s24",
        //      "uuid": "b57a076a-fd5f-ca1c-28e5-53b2dc852365",
        //      "status": "assigned",
        //      "date": "2023-10-03",
        //      "datetimeBegin": "2023-10-03 10:00:00",
        //      "datetimeEnd": "2023-10-03 20:00:00",
        //      "Intervals": [
        //        {
        //          "type_uuid": "389b38d3-aba2-4958-e586-6e8513fb63d5",
        //          "start_time": 36000,
        //          "end_time": 72000,
        //          "lunch_time": 3600
        //        }
        //      ],
        //      "fact": [
        //        {
        //          "id": 123124,
        //          "type": "closed",
        //          "begin": "2023-10-03 10:00:00",
        //          "end": "2023-10-03 20:00:00"
        //        }
        //      ],
        //      "planFactIntervals": [
        //        {
        //          "begin": "2023-10-03 10:00:00",
        //          "end": "2023-10-03 20:00:00"
        //        }
        //      ],
        //      "contractParamsClarity": {
        //        "name": "Название связки условий и параметров контракта",
        //        "conditions": {
        //          "organization_uuids": [
        //            "5d3a5e5d-4320-403d-bb96-a783ce4db4af"
        //          ],
        //          "service_speciality_uuids": [
        //            "2c6c9a49-6c6b-4cb6-894f-8c99fb4f51a0"
        //          ],
        //          "speciality_uuids": [
        //            "5d3a5e5d-4320-403d-bb96-a783ce4db4af"
        //          ]
        //        },
        //        "parameters": [
        //          {
        //            "type": "document_type_groups",
        //            "data": {
        //              "groups": [
        //                {
        //                  "employee_citizenship": [
        //                    "Гражданин РФ"
        //                  ],
        //                  "optional_document_types": [
        //                    "personal_medical_book"
        //                  ],
        //                  "required_document_types": [
        //                    "Паспорт"
        //                  ]
        //                },
        //                {
        //                  "employee_citizenship": [
        //                    "Иностранный гражданин"
        //                  ],
        //                  "optional_document_types": [
        //                    "personal_medical_book"
        //                  ],
        //                  "required_document_types": [
        //                    "Паспорт"
        //                  ]
        //                }
        //              ]
        //            }
        //          },
        //          {
        //            "type": "assigned_employee_restrictions",
        //            "data": {
        //              "gender": {
        //                "type": "male"
        //              }
        //            }
        //          }
        //        ]
        //      },
        //      "unitUuid": "5d3a5e5d-4320-403d-bb96-a783ce4db4af",
        //      "objectUuids": [
        //        "6d3s3q56-r125-65fd-bg61-j853fgf1b4af"
        //      ],
        //      "specialityUuid": "656051df-f6a9-f21d-a62a-0fac57ddd0a2",
        //      "userCreatedUuid": "4ba17c1e-a9d2-4578-9613-0c667cc848ed",
        //      "resources": {
        //        "units": {
        //          "5d3a5e5d-4320-403d-bb96-a783ce4db4af": {
        //            "uuid": "5d3a5e5d-4320-403d-bb96-a783ce4db4af",
        //            "name": "Торговый зал-123"
        //          }
        //        },
        //        "specialities": {
        //          "656051df-f6a9-f21d-a62a-0fac57ddd0a2": {
        //            "uuid": "656051df-f6a9-f21d-a62a-0fac57ddd0a2",
        //            "name": "Специалист по приему товара"
        //          }
        //        },
        //        "objects": {
        //          "6d3s3q56-r125-65fd-bg61-j853fgf1b4af": {
        //            "uuid": "6d3s3q56-r125-65fd-bg61-j853fgf1b4af",
        //            "title": "Лента-123",
        //            "latitude": "56.3071320000000000",
        //            "longitude": "43.9979550000000000",
        //            "address": "Россия, Москва, Осенний бульвар, 12",
        //            "timezone": "Europe/Moscow"
        //          }
        //        }
        //      }
        //    }
        //  ],
        //  "employees": {
        //    "4ba17c1e-a9d2-4578-9613-0c667cc848ed": {
        //      "uuid": "4ba17c1e-a9d2-4578-9613-0c667cc848ed",
        //      "natural_person": {
        //        "uuid": "4ba17c1e-a9d2-4578-9613-0c667cc848ed",
        //        "last_name": "Иванов",
        //        "first_name": "Иван",
        //        "second_name": "Иванович",
        //        "birth_date": "12.06.1987",
        //        "phone_number ": "+79969231233"
        //      }
        //    }
        //  }
        //}
    }

    public function getPrefix():string
    {
        return 't_';
    }

    public function getType():int
    {
        return 3;
    }
}
