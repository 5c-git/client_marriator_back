<?php

namespace App\Services\Local\Repositories\Contracts;

use App\Enum\User\SortEnum;
use App\Enum\User\UserStatusModerationEnum;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

interface UserRepository
{
    public function getModerationUsers(array $roles = []): Collection;

    public function getModerationUsersPaginate(array $roles = [],
                                               SortEnum $sort = SortEnum::all,
                                               UserStatusModerationEnum $status = UserStatusModerationEnum::new ,
                                               int $page = 1,
                                               int $perPage = 10
    ): Paginator;

}
