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

        Route::group(['prefix' => 'directory_country'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryCreate')->name('countryCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryCreateAjax')->name('countryCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryList')->name('countryList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryEdit')->name('countryEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryEditAjax')->name('countryEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CountryController@countryDelete')->name('countryDelete');
        });

        Route::group(['prefix' => 'directory_bank'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankCreate')->name('bankCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankCreateAjax')->name('bankCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankList')->name('bankList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankEdit')->name('bankEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankEditAjax')->name('bankEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\BankController@bankDelete')->name('bankDelete');
        });

    });
});
