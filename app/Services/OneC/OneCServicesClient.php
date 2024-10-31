<?php

namespace App\Services\OneC;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class OneCServicesClient
{

    private $url = [
        'sendReg' => '',
        'updateUserData' => '',
        'updateUserRequisites' => '',
        'getUserRequisites' => '',
        'setTerminate' => '',
        'setConclude' => '',
        'requestInquiries' => '',
        'ping' => '',
    ];

    private $host = '';
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function sendRegister(): array
    {
        $userData = json_decode($this->user->data,true);
        $data = [
            'userId' => $this->user->id,
            'data' => $userData,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['sendReg'],$data);
        $userUuid = '';
        if(!empty($responseData['user']['uuid'])) {
            $userUuid = $responseData['user']['uuid'];
        }
        $status = $responseData['status'] == 'success';
        return [$status,$userUuid];
    }

    public function sendUpdateUserData($updateData): bool
    {
        $userData = $updateData;
        if(empty($userData)){
            $userData = [];
        }
        $data = [
            'userId' => $this->user->id,
            'data' => $userData,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['updateUserData'],$data);
        return $responseData['status'] == 'success';
    }

    public function sendUpdateUserRequisites($updateData): bool
    {
        $userData = $updateData;
        if(empty($userData)){
            $userData = [];
        }
        $data = [
            'userId' => $this->user->id,
            'data' => $userData,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['updateUserRequisites'],$data);
        return $responseData['status'] == 'success';
    }

    public function setTerminate($terminate): bool
    {
        $data = [
            'userId' => $this->user->id,
            'data' => $terminate,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['setTerminate'],$data);
        return $responseData['status'] == 'success';
    }

    public function setConclude($conclude): bool
    {
        $data = [
            'userId' => $this->user->id,
            'data' => $conclude,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['setConclude'],$data);
        return $responseData['status'] == 'success';
    }

    public function requestInquiries($requestInquiries): bool
    {
        $data = [
            'userId' => $this->user->id,
            'data' => $requestInquiries,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['requestInquiries'],$data);
        return $responseData['status'] == 'success';
    }

    public function getUserRequisites(): bool
    {
        $data = [
            'userId' => $this->user->id,
        ];
        $responseData = $this->sendGet($this->url['getUserRequisites'],$data);
        return $responseData['status'] == 'success';
    }

    public function ping(){
        $responseData = $this->sendPost($this->url['ping']);
        return $responseData['status'] == 'success';
    }

    public function sendPost(string $url,array $data = []): array
    {
        return [
            'status'=>'success',
            'user'=>[
                'uuid'=>uniqid()
            ]
        ];
        $response = Http::withHeaders([])->post($this->host.$url,$data);
        if($response->successful()){
            return ['status'=>'success',...$response->body()];
        }else{
            return ['status'=>'error',...$response->body()];
        }
    }

    public function sendGet(string $url,array $data = []){
        return ['status'=>'success'];
        $response = Http::withHeaders([])->get($this->host.$url,$data);
        if($response->successful()){
            return ['status'=>'success',...$response->body()];
        }else{
            return ['status'=>'error',...$response->body()];
        }
    }

}
