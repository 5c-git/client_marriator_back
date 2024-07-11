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
});

Route::group(["middleware" => ["auth:api","scope:register,restorePin,checkPin,personalArea"]], function () {
    Route::post('/setUserPin/', 'App\Http\Controllers\Form\RegistrationController@setUserPin')->name('setUserPin');
    Route::post('/startRestorePin/', 'App\Http\Controllers\Form\RegistrationController@startRestorePin')->name('startRestorePin');
    Route::post('/checkCodeRestore/', 'App\Http\Controllers\Form\RegistrationController@checkCodeRestore')->name('checkCodeRestore');
});

Route::group(["middleware" => ["auth:api","scope:checkPin"]], function () {
    Route::post('/checkPin/', 'App\Http\Controllers\PersonalArea\CheckPinController@checkPin')->name('checkPin');
});

Route::group(["middleware" => ["auth:api","scope:personalArea"]], function () {
    Route::group(['prefix' => 'personal'], function () {
    Route::get('/getUserInfo/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getUserInfo')->name('getUserInfo');
    Route::get('/getUserFields/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getUserFields')->name('getUserFields');
    Route::post('/saveUserImg/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveUserImg')->name('saveUserImg');
    });
});



