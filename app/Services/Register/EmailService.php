<?php

namespace App\Services\Register;

use App\Mail\ApplicationApproved;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class EmailService
{
    public static function sendCode(string $email, string $code){
        return true;
    }

    public static function sendConfirmUserModeration(User $user): void
    {
        Mail::to($user->email)->send(
            new ApplicationApproved($user)
        );
    }

}
