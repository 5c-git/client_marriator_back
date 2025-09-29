<?php

namespace App\Services\Verme;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VermeService
{
    public function __construct()
    {

    }

    static function sendUserInfo(User $user): bool
    {
        return true;
    }
}
