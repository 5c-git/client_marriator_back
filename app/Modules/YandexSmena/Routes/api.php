<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use Modules\YandexSmena\Http\Controllers\PublishYandexSmenaShiftController;

Route::prefix('api')->middleware(['auth:api', 'scope:personalArea', CheckRole::class.':manager,supervisor'])->group(function () {
    Route::post('/yandex-smena/publish-shift', PublishYandexSmenaShiftController::class)
        ->name('yandex-smena.publish-shift');
});
