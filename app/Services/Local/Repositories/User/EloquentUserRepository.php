<?php

namespace App\Services\Local\Repositories\User;

use App\Models\User;
use App\Services\Local\Repositories\Contracts\UserRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository implements UserRepository
{
    public function getModerationUsers(array $roles = []): Collection
    {
       return User::query()->orderBy('id', 'desc')
            ->where('confirmRegister',false)
            ->where('finishRegister',true)
            ->with(['roles','project','place'])
            ->when($roles, function (Builder $q, array $roles) {
                $q->whereHas('roles', function ($query) use ($roles)  {
                    $query->whereIn('role_id', $roles);
                });
            })
            ->get();
    }

}
