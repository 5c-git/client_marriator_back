<?php

namespace App\Services\Local\Repositories\User;

use App\Enum\User\SortEnum;
use App\Enum\User\UserStatusModerationEnum;
use App\Models\User;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Carbon\Carbon;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class CachingUserRepository implements UserRepository
{
    public function __construct(
        protected UserRepository $users,
        protected CacheManager $cache,
    ) {
    }

    public function getModerationUsers(int $userId, array $roles = [])
    {
        return $this->users->getModerationUsers($userId, $roles);
    }

    public function getModerationUsersPaginate(array $roles = [],
                                               SortEnum $sort = SortEnum::all,
                                               UserStatusModerationEnum $status = null,
                                               int $page = 1,
                                               int $perPage = 10
    ): Paginator
    {
        return $this->users->getModerationUsersPaginate($roles,$sort,$status,$page,$perPage);
    }


    public function getModerationUser(int $userId, array $roles = []): User
    {
        return $this->users->getModerationUser($userId,$roles);
    }
}
