<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface UserRepository
{
    public function getModerationUsers(array $roles = []): Collection;

}
