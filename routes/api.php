<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::match(['get', 'post'],'/getForm/', 'App\Http\Controllers\Form\FormController@getForm')->name('getForm');
Route::post('/saveFile/', 'App\Http\Controllers\Form\FormController@saveFile')->name('saveFile');

