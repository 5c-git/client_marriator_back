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
    public OneCServicesClient $oneCServicesClient;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->oneCServicesClient = new OneCServicesClient($user);
    }

    public function sendRegister(){
        $this->status = $this->oneCServicesClient->sendRegister();
        return $this;
    }

    public function updateUserData(array $updateField){
        $this->status = $this->oneCServicesClient->sendUpdateUserData($updateField);
        return $this;
    }

}
