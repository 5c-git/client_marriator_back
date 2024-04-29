<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckPermission;

Route::group(['prefix' => 'admin'], function () {

    Route::get('/login/', function () {
        return view('admin.login');
    })->name('adminLogin');

    Route::post('/loginAdminAjax/', 'App\Http\Controllers\Admin\Auth\LoginController@customAdminLogin')->name('loginAdminAjax');

    Route::middleware([CheckPermission::class])->group( function () {
        Route::get('/','App\Http\Controllers\Admin\Page\MainPageController@mainPage')->name('mainPage');

        Route::match(['get', 'post'], '/logout', 'App\Http\Controllers\Admin\Auth\LoginController@logout')->name('logout');

        Route::group(['prefix' => 'users'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\UsersController@usersCreate')->name('usersCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\UsersController@usersCreateAjax')->name('usersCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\UsersController@usersList')->name('usersList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\UsersController@userEdit')->name('userEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\UsersController@userEditAjax')->name('userEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\UsersController@userDelete')->name('userDelete');
        });

        Route::group(['prefix' => 'fields'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsCreate')->name('fieldsCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsCreateAjax')->name('fieldsCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsList')->name('fieldsList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsEdit')->name('fieldsEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsEditAjax')->name('fieldsEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\FieldsController@fieldsDelete')->name('fieldsDelete');
        });

    });
});
