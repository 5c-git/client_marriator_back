<?php

namespace App\Services\Local\Repositories\User;

use App\Enum\User\SortEnum;
use App\Enum\User\UserStatusModerationEnum;
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
use Illuminate\Contracts\Pagination\Paginator;

class EloquentUserRepository implements UserRepository
{
    public function getModerationUsers(int $userId, array $roles = [])
    {
       return User::query()->orderBy('id', 'desc')
            //->where('confirmRegister',false)
            //->where('finishRegister',true)
            ->where('id',$userId)
            ->with(['roles'])
            ->when($roles, function (Builder $q, array $roles) {
                $q->whereHas('roles', function ($query) use ($roles)  {
                    $query->whereIn('role_id', $roles);
                });
            })
            ->first();
    }


    public function getModerationUsersPaginate(array $roles = [],SortEnum $sort = SortEnum::all,UserStatusModerationEnum $status = null,int $page = 1,int $perPage = 10): Paginator
    {
        $userQuery = User::query()
            ->with(['roles','project','place']);
        if($roles) {
            $userQuery = $userQuery->when($roles, function (Builder $q, array $roles) {
                $q->whereHas('roles', function ($query) use ($roles) {
                    $query->whereIn('role_id', $roles);
                });
            });
        }else{
            $userQuery = $userQuery->where('phone','123');
        }
        if($status == UserStatusModerationEnum::archive){
            $userQuery = $userQuery->where('confirmRegister',false)
                ->where('finishRegister',false);
        }
        if($status == UserStatusModerationEnum::new){
            $userQuery = $userQuery->where('confirmRegister',false)
                ->where('finishRegister',true);
        }
        if($status == UserStatusModerationEnum::inProgress){
            $userQuery = $userQuery->where('confirmRegister',true)
                ->where('finishRegister',true);
        }
        if($sort == SortEnum::new){
            $userQuery = $userQuery->orderBy('id','desc');
        }
        if($sort == SortEnum::old){
            $userQuery = $userQuery->orderBy('id','asc');
        }


       return $userQuery->simplePaginate($perPage);
    }

    public function getModerationUser(int $userId, array $roles = []): User
    {
        $userQuery = User::query()
            ->with(['roles','project','place']);
        if($roles) {
            $userQuery = $userQuery->when($roles, function (Builder $q, array $roles) {
                $q->whereHas('roles', function ($query) use ($roles) {
                    $query->whereIn('role_id', $roles);
                });
            });
        }else{
            $userQuery = $userQuery->where('phone','123');
        }
        $userQuery = $userQuery->where('id',$userId);

        return $userQuery->first();
    }
}
