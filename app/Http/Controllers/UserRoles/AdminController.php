<?php

namespace App\Http\Controllers\UserRoles;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Fields\Fields;
use App\Models\User\Role;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Http\Resources\UserResource;
use App\Enum\Role\RoleEnum;
use App\Http\Requests\ConfirmUserRequest;
use App\Services\Local\Repositories\Contracts\UserRepository;
use App\Http\Resources\SuccessResource;
use App\Http\Requests\PaginatorRequest;

class AdminController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function getDataProject(){
        $userProject = Auth::user()->project;
        return new ProjectResource($userProject);
    }

    public function getModerationClient(PaginatorRequest $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $arrRoleConfirm = [];
        foreach ($userRoles as $role){
            $arrRoleConfirm = RoleEnum::from($role)->getClientForModeration();
        }
        $arrRoleConfirm = array_unique($arrRoleConfirm);

        $usersForModeration = $this->userRepository->getModerationUsersPaginate($arrRoleConfirm,
            $request->input('page', 1),
            $request->input('perPage', 10),
        );

        return UserResource::collection($usersForModeration);
    }

    public function confirmUserRegister(ConfirmUserRequest $request): SuccessResource
    {
        $user = Auth::user();
        $userRoles = $user->roles?->pluck('id')->toArray();
        $arrRoleConfirm = [];
        foreach ($userRoles as $role){
            $arrRoleConfirm = RoleEnum::from($role)->getClientForModeration();
        }
        $arrRoleConfirm = array_unique($arrRoleConfirm);

        $userForModeration = $this->userRepository
            ->getModerationUsers($arrRoleConfirm)?->where('id',$request->userId)?->first();
        if(!empty($userForModeration)){
            if($request->confirm){
                $userForModeration->confirmRegister = true;
                if($request->supervisorIds) {
                    $userForModeration->supervisors()->sync($request->supervisorIds);
                }
            }else{
                $userForModeration->finishRegister = false;
            }
            $userForModeration->save();
        }

        return new SuccessResource();
    }

}
