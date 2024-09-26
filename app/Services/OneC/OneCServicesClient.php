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
        'ping' => '',
    ];

    private $host = '';
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function sendRegister(): bool
    {
        $userData = json_decode($this->user->data,true);
        $data = [
            'userId' => $this->user->id,
            'data' => $userData,
            'date' => Carbon::now(),
        ];
        $responseData = $this->sendPost($this->url['sendReg'],$data);
        return $responseData['status'] == 'success';
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

    public function ping(){
        $responseData = $this->sendPost($this->url['ping']);
        return $responseData['status'] == 'success';
    }

    public function sendPost(string $url,array $data = []){
        return ['status'=>'success'];
        $response = Http::withHeaders([])->post($this->host.$url,$data);
        if($response->successful()){
            return ['status'=>'success',$response->body()];
        }else{
            return ['status'=>'error',$response->body()];
        }
    }

}
