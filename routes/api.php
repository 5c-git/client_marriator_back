<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => 'throttle:10,10'], function () {
Route::post('/sendPhone/', 'App\Http\Controllers\Form\RegistrationController@sendPhone')->name('sendPhone');
Route::post('/checkCode/', 'App\Http\Controllers\Form\RegistrationController@checkCode')->name('checkCode');
});
Route::group(["middleware" => ["auth:api","scope:register"]], function () {
    Route::get('/getForm/', 'App\Http\Controllers\Form\FormController@getForm')->name('getForm');
    Route::post('/saveForm/', 'App\Http\Controllers\Form\FormController@saveForm')->name('saveForm');
    Route::post('/saveUserImg/', 'App\Http\Controllers\Form\FormController@saveUserImg')->name('saveUserImg');
    Route::post('/saveFile/', 'App\Http\Controllers\Form\FormController@saveFile')->name('saveFile');
    Route::post('/setUserPin/', 'App\Http\Controllers\Form\RegistrationController@setUserPin')->name('setUserPin');
});

