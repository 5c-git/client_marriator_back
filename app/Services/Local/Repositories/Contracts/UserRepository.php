<?php

namespace App\Services\Local\Repositories\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

interface UserRepository
{
    public function getModerationUsers(array $roles = []): Collection;

    public function getModerationUsersPaginate(array $roles = [], int $page = 1,int $perPage = 10): Paginator;

}
