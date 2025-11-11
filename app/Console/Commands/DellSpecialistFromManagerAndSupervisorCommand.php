<?php

namespace App\Console\Commands;


use App\Enum\Order\ReportStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Models\Order\Report;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DellSpecialistFromManagerAndSupervisorCommand extends Command
{
    protected $signature = 'dellSpecialistFromManagerAndSupervisorCommand';

    protected $description = '';

    public function handle(): void
    {
        $cutoffDate = Carbon::now()->subDays(60);

        $inactiveUserIds = User::whereHas('roles', function($query) {
            $query->where('id', RoleEnum::specialist->value);
        })->whereDoesntHave('reports', function($query) use ($cutoffDate) {
            $query->where('date_start', '>=', $cutoffDate);
        })->pluck('id');

        $bidUserIds = DB::table('report')
            ->join('bids', 'report.bid_id', '=', 'bids.id')
            ->whereIn('report.user_id', $inactiveUserIds)
            ->pluck('bids.user_id')
            ->unique();

        DB::table('manager_specialist')
            ->whereIn('user_id_specialist', $inactiveUserIds)
            ->whereIn('user_id_manager', $bidUserIds)
            ->delete();

        DB::table('supervisor_specialist')
            ->whereIn('user_id_specialist', $inactiveUserIds)
            ->whereIn('user_id_supervisor', $bidUserIds)
            ->delete();
    }
}
