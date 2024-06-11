<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/getForm/', 'App\Http\Controllers\Form\FormController@getForm')->name('getForm');
Route::post('/saveForm/', 'App\Http\Controllers\Form\FormController@saveForm')->name('saveForm');
Route::post('/saveUserImg/', 'App\Http\Controllers\Form\FormController@saveUserImg')->name('saveUserImg');
Route::post('/saveFile/', 'App\Http\Controllers\Form\FormController@saveFile')->name('saveFile');

