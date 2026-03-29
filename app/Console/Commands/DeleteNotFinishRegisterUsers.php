<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
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
                ->where('updated_at', '>=', Carbon::now()->subDays($days))
                ->get();
            foreach ($users as $user) {
               $user->delete();
            }
        }
    }
}
