<?php

namespace App\Console\Commands;


use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class DellAllUpdateDataCommand extends Command
{
    protected $signature = 'dellAllUpdateDataCommand';

    protected $description = '';


    public function handle(): void
    {
        $users = User::query()->get();
        foreach ($users as $user) {
            $user->updateData = json_encode([]);
            $user->change_fields = json_encode([]);
            $user->date_for_send = null;
            $user->save();
        }
    }

}
