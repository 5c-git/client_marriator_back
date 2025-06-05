<?php

namespace App\Services\Local\Repositories\User;

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

    public function getModerationUsers(array $roles = []): Collection
    {
        return $this->users->getModerationUsers($roles);
    }

    public function getModerationUsersPaginate(array $roles = [],UserStatusModerationEnum $status = UserStatusModerationEnum::new,int $page = 1,int $perPage = 10): Paginator
    {
        return $this->users->getModerationUsersPaginate($roles,$status,$page,$perPage);
    }



}
