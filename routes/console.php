<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//Schedule::command('sendUpdateDataUserCommand')->everyMinute()->withoutOverlapping();
Schedule::command('endedReportCommand')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('dellSpecialistFromManagerAndSupervisorCommand')->daily()->withoutOverlapping();
Schedule::command('closeBidCommand')->hourly()->withoutOverlapping();

Schedule::command('sendUserFileToCorrect')->everyThreeMinutes()->withoutOverlapping();
Schedule::command('getUserFileFromCorrect')->everyThreeMinutes()->withoutOverlapping();

Schedule::command('getUserReportCoefficient')->everyMinute()->withoutOverlapping();
Schedule::command('archiveBid')->everyThreeMinutes()->withoutOverlapping();
Schedule::command('archiveTask')->everyThreeMinutes()->withoutOverlapping();
Schedule::command('archiveOrder')->everyThreeMinutes()->withoutOverlapping();
Schedule::command('inWorkBid')->everyThreeMinutes()->withoutOverlapping();




