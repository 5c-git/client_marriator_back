<?php

namespace App\Services\OneC;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\OneC\OneCServicesClient;

class OneCServices
{

    private User $user;
    public bool $status;
    public string $uuid;
    public OneCServicesClient $oneCServicesClient;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->oneCServicesClient = new OneCServicesClient($user);
    }

    public function sendRegister(): static
    {
        [$this->status,$this->uuid] = $this->oneCServicesClient->sendRegister();
        return $this;
    }

    public function updateUserData(array $updateField){
        $this->status = $this->oneCServicesClient->sendUpdateUserData($updateField);
        return $this;
    }

    public function sendUpdateUserRequisites(array $updateField){
        $this->status = $this->oneCServicesClient->sendUpdateUserRequisites($updateField);
        return $this;
    }

    public function setTerminate(array $terminate){
        $this->status = $this->oneCServicesClient->setTerminate($terminate);
        return $this;
    }

    public function setConclude(array $conclude){
        $this->status = $this->oneCServicesClient->setConclude($conclude);
        return $this;
    }

    public function requestInquiries(array $requestInquiries){
        $this->status = $this->oneCServicesClient->requestInquiries($requestInquiries);
        return $this;
    }

    public function getUserRequisites(){
        $this->status = $this->oneCServicesClient->getUserRequisites();
        return $this;
    }

}
