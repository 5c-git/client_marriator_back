<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class DeleteNotFinishRegisterUsers extends Command
{
    protected $signature = 'deleteNotFinishRegisterUsers';

    protected $description = '';

    public function handle(): void
    {
        $days = Setting::getValue('delNotFinishUser');
        if(!empty($days) && is_numeric($days)) {
            $users = User::query()
                ->where('finishRegister', false)
                ->where('updated_at', '<=', Carbon::now()->subDays($days))
                ->whereHas('roles', function ($query) {
                    $query->where('role_id','!=',RoleEnum::admin->value);
                })
                ->get();
            foreach ($users as $user) {
                $user->delete();
            }
        }

        $users = User::where('confirmRegister', true)
            ->where('finishRegister', true)
            ->whereHas('project')
            ->whereDoesntHave('project', function ($query) {
                $query->where('date_end', '>', Carbon::now());
            })
            ->get();
        foreach ($users as $user) {
            $user->confirmRegister = false;
            $user->finishRegister = false;
            $user->save();
        }




    }
}
