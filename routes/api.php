<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => 'throttle:100,10'], function () {
    Route::post('/sendPhone/', 'App\Http\Controllers\Form\RegistrationController@sendPhone')->name('sendPhone');
    Route::get('/login', function () {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    })->name('login');
    Route::post('/checkCode/', 'App\Http\Controllers\Form\RegistrationController@checkCode')->name('checkCode');
    Route::post('/refreshToken/', 'App\Http\Controllers\PersonalArea\CheckPinController@refreshToken')->name('refreshToken');

});

Route::group(["middleware" => ["auth:api","scope:register"]], function () {
    Route::get('/getForm/', 'App\Http\Controllers\Form\FormController@getForm')->name('getForm');
    Route::post('/saveForm/', 'App\Http\Controllers\Form\FormController@saveForm')->name('saveForm');
    Route::post('/saveUserImg/', 'App\Http\Controllers\Form\FormController@saveUserImg')->name('saveUserImg');
    Route::post('/saveFile/', 'App\Http\Controllers\Form\FormController@saveFile')->name('saveFile');
    Route::post('/finishRegister/', 'App\Http\Controllers\Form\FormController@finishRegister')->name('finishRegister');
    Route::post('/setUserPin/', 'App\Http\Controllers\Form\RegistrationController@setUserPin')->name('setUserPin');
});

Route::group(["middleware" => ["auth:api","scope:checkPin"]], function () {
    Route::post('/checkPin/', 'App\Http\Controllers\PersonalArea\CheckPinController@checkPin')->name('checkPin');
});


Route::group(["middleware" => ["auth:api","scope:personalArea"]], function () {
    Route::get('/personalInfo/', 'App\Http\Controllers\PersonalArea\CheckPinController@checkPin')->name('personalInfo');
});



