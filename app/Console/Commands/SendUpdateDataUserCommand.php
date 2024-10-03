<?php

namespace App\Console\Commands;


use App\Services\OneC\OneCServices;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;

class SendUpdateDataUserCommand extends Command
{
    protected $signature = 'sendUpdateDataUserCommand';

    protected $description = 'send Update Data User';


    public function handle(): void
    {
        $users = User::query()->whereNotNull('date_for_send')->where('date_for_send','<',Carbon::now()->subMinutes(10))->get();
        foreach ($users as $user) {
            if (!empty($user->change_fields)) {
                $user->change_fields = json_decode($user->change_fields, true);
            } else {
                $user->change_fields = [];
            }
            $updateResult = (new OneCServices($user))->updateUserData($user->change_fields);
            if ($updateResult->status) {
                $user->updateData = json_encode($user->change_fields);
                $user->change_fields = json_encode([]);
                $user->date_for_send = null;
            }
            $user->save();
        }
    }

}
