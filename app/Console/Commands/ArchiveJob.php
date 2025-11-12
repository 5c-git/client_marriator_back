<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\OrderActivities;
use App\Models\Order\Report;
use App\Models\Order\Task;
use App\Models\Order\TaskActivity;
use App\Models\Setting;
use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArchiveJob extends Command
{
    protected $signature = 'archiveJob';

    protected $description = '';

    public function handle(): void
    {
        DB::table('accept_bid')
            ->join('users', 'accept_bid.user_id', '=', 'users.id')
            ->whereRaw('DATE_ADD(accept_bid.created_at, INTERVAL users.time_answer_bid HOUR) > NOW()')
            ->update(['accept_bid.status' => 7]);
    }
}
