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

class OneCServices
{

    private User $user;
    public bool $statusRegister;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function sendRegister(){
        //$this->user;
        $this->statusRegister = true;
        return $this;
    }

}
