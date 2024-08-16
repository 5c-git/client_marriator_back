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

Route::group(["middleware" => ["auth:api", "scope:register"]], function () {
    Route::get('/getUserInfo/', 'App\Http\Controllers\Form\FormController@getUserInfo')->name('getUserInfoInReg');
    Route::get('/getForm/', 'App\Http\Controllers\Form\FormController@getForm')->name('getForm');
    Route::post('/saveForm/', 'App\Http\Controllers\Form\FormController@saveForm')->name('saveForm');
    Route::post('/saveUserImg/', 'App\Http\Controllers\Form\FormController@saveUserImg')->name('saveUserImg');
    Route::post('/finishRegister/', 'App\Http\Controllers\Form\FormController@finishRegister')->name('finishRegister');

    Route::post('/setUserEmail/', 'App\Http\Controllers\Form\RegistrationController@setUserEmail')->name('setUserEmail_reg');
    Route::post('/checkEmailCode/', 'App\Http\Controllers\Form\RegistrationController@checkEmailCode')->name('checkEmailCode_reg');
});
Route::group(["middleware" => ["auth:api", "scope:register,personalArea"]], function () {
    Route::post('/saveFile/', 'App\Http\Controllers\Form\FormController@saveFile')->name('saveFile');
});

Route::group(["middleware" => ["auth:api", "scope:register,restorePin,checkPin,personalArea"]], function () {
    Route::post('/setUserPin/', 'App\Http\Controllers\Form\RegistrationController@setUserPin')->name('setUserPin');
    Route::post('/startRestorePin/', 'App\Http\Controllers\Form\RegistrationController@startRestorePin')->name('startRestorePin');
    Route::post('/checkCodeRestore/', 'App\Http\Controllers\Form\RegistrationController@checkCodeRestore')->name('checkCodeRestore');
});

Route::group(["middleware" => ["auth:api", "scope:checkPin"]], function () {
    Route::post('/checkPin/', 'App\Http\Controllers\PersonalArea\CheckPinController@checkPin')->name('checkPin');
});

Route::group(["middleware" => ["auth:api", "scope:personalArea"]], function () {
    Route::group(['prefix' => 'personal'], function () {
        Route::get('/getUserInfo/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getUserInfo')->name('getUserInfo');
        Route::get('/getUserFields/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getUserFields')->name('getUserFields');
        Route::get('/getUserPersonalMenu/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getUserPersonalMenu')->name('getUserPersonalMenu');
        Route::post('/saveUserFields/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveUserFields')->name('saveUserFields');
        Route::post('/saveUserImg/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveUserImg')->name('saveUserImgPersonal');
        Route::post('/setUserEmail/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@setUserEmail')->name('setUserEmail');
        Route::post('/checkEmailCode/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@checkEmailCode')->name('checkEmailCode');

        Route::post('/changeUserPhone/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@changeUserPhone')->name('changeUserPhone');
        Route::post('/confirmChangeUserPhone/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@confirmChangeUserPhone')->name('confirmChangeUserPhone');

        Route::get('/getRequisitesData/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getRequisitesData')->name('getRequisitesData');
        Route::get('/getEstateData/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getEstateData')->name('getEstateData');

        Route::post('/saveRequisitesData/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveRequisitesData')->name('saveRequisitesData');
        Route::post('/saveEstateData/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveEstateData')->name('saveEstateData');

        Route::get('/getformActivities/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getEstateData')->name('getformActivities');
        Route::post('/saveUserFieldsActivities/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getEstateData')->name('saveUserFieldsActivities');
    });
});



