<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Enum\User\SortEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

interface UserRepository
{
    public function getModerationUsers(int $userId, array $roles = []);

    public function getModerationUsersPaginate(array $roles = [],
                                               SortEnum $sort = SortEnum::all,
                                               UserStatusModerationEnum $status = null,
                                               int $page = 1,
                                               int $perPage = 10
    ): Paginator;

    public function getModerationUser(int $userId, array $roles = []): User;

}
