<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('sendUpdateDataUserCommand')->everyMinute();
Schedule::command('recognition:process')->everyMinute()->withoutOverlapping();
Schedule::command('endedReportCommand')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('dellSpecialistFromManagerAndSupervisorCommand')->daily()->withoutOverlapping();
Schedule::command('closeBidCommand')->hourly()->withoutOverlapping();




