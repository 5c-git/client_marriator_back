<?php

use Illuminate\Support\Facades\Route;
use Modules\Questionnaire\Http\Controllers\QuestionnaireController;

Route::prefix('api/questionnaire')->middleware('api')->group(function () {
    Route::post('/start/{user}', [QuestionnaireController::class, 'start'])->name('questionnaire.start');
    Route::get('/status/{user}', [QuestionnaireController::class, 'status'])->name('questionnaire.status');
    Route::get('/result/{user}', [QuestionnaireController::class, 'result'])->name('questionnaire.result');
});
