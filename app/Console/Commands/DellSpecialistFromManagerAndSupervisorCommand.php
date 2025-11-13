<?php

namespace App\Console\Commands;


use App\Enum\Order\ReportStatusEnum;
use App\Enum\Role\RoleEnum;
use App\Models\Order\Report;
use App\Models\Setting;
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
        $cutoffDate = Carbon::now()->subDays(Setting::getValue('waiting_task'));

        $inactiveUserIds = User::whereHas('roles', function($query) {
            $query->where('id', RoleEnum::specialist->value);
        })->whereDoesntHave('reports', function($query) use ($cutoffDate) {
            $query->where('date_start', '>=', $cutoffDate);
        })->pluck('id');

        if ($inactiveUserIds->isEmpty()) {
            return;
        }

        // Получаем пары (специалист, менеджер) из отчетов через bids
        $specialistManagerPairs = DB::table('report')
            ->join('bids', 'report.bid_id', '=', 'bids.id')
            ->whereIn('report.user_id', $inactiveUserIds)
            ->select('report.user_id as specialist_id', 'bids.user_id as manager_id')
            ->distinct()
            ->get();

        // Получаем пары (специалист, супервайзер) из отчетов через bids
        $specialistSupervisorPairs = DB::table('report')
            ->join('bids', 'report.bid_id', '=', 'bids.id')
            ->whereIn('report.user_id', $inactiveUserIds)
            ->select('report.user_id as specialist_id', 'bids.user_id as supervisor_id')
            ->whereNotNull('bids.user_id')
            ->distinct()
            ->get();

        // Удаляем только конкретные пары специалист-менеджер
        foreach ($specialistManagerPairs as $pair) {
            DB::table('manager_specialist')
                ->where('user_id_specialist', $pair->specialist_id)
                ->where('user_id_manager', $pair->manager_id)
                ->delete();
        }

        // Удаляем только конкретные пары специалист-супервайзер
        foreach ($specialistSupervisorPairs as $pair) {
            DB::table('supervisor_specialist')
                ->where('user_id_specialist', $pair->specialist_id)
                ->where('user_id_supervisor', $pair->supervisor_id)
                ->delete();
        }
    }
}
