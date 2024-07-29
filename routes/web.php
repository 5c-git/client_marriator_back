<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckPermission;


Route::group([
    'as' => 'passport.',
    'prefix' => config('passport.path', 'oauth'),
    'namespace' => '\Laravel\Passport\Http\Controllers',
], function () {
    Route::post('/token', [
        'uses' => 'AccessTokenController@issueToken',
        'as' => 'token',
        'middleware' => 'throttle',
    ]);
});

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

        Route::group(['prefix' => 'directory_activities'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@create')->name('activitiesCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@createAjax')->name('activitiesCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@list')->name('activitiesList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@edit')->name('activitiesEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@editAjax')->name('activitiesEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ActivitiesController@delete')->name('activitiesDelete');
        });

        Route::group(['prefix' => 'directory_tax_status'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@create')->name('taxStatusCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@createAjax')->name('taxStatusCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@list')->name('taxStatusList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@edit')->name('taxStatusEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@editAjax')->name('taxStatusEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\TaxStatusController@delete')->name('taxStatusDelete');
        });

        Route::group(['prefix' => 'directory_citizenship'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@create')->name('citizenshipCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@createAjax')->name('citizenshipCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@list')->name('citizenshipList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@edit')->name('citizenshipEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@editAjax')->name('citizenshipEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\CitizenshipController@delete')->name('citizenshipDelete');
        });

        Route::group(['prefix' => 'directory_residence'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@create')->name('residenceCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@createAjax')->name('residenceCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@list')->name('residenceList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@edit')->name('residenceEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@editAjax')->name('residenceEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ResidenceController@delete')->name('residenceDelete');
        });

        Route::group(['prefix' => 'directory_region_of_residence'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@create')->name('region_of_residenceCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@createAjax')->name('region_of_residenceCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@list')->name('region_of_residenceList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@edit')->name('region_of_residenceEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@editAjax')->name('region_of_residenceEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\RegionOfResidenceController@delete')->name('region_of_residenceDelete');
        });
        Route::group(['prefix' => 'directory_offer_search'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@create')->name('offer_searchCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@createAjax')->name('offer_searchCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@list')->name('offer_searchList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@edit')->name('offer_searchEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@editAjax')->name('offer_searchEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OfferSearchController@delete')->name('offer_searchDelete');
        });
        Route::group(['prefix' => 'directory_view_activities'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@create')->name('view_activitiesCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@createAjax')->name('view_activitiesCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@list')->name('view_activitiesList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@edit')->name('view_activitiesEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@editAjax')->name('view_activitiesEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ViewActivitiesController@delete')->name('view_activitiesDelete');
        });
        Route::group(['prefix' => 'directory_weight'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@create')->name('weightCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@createAjax')->name('weightCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@list')->name('weightList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@edit')->name('weightEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@editAjax')->name('weightEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\WeightController@delete')->name('weightDelete');
        });
        Route::group(['prefix' => 'directory_height'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@create')->name('heightCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@createAjax')->name('heightCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@list')->name('heightList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@edit')->name('heightEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@editAjax')->name('heightEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HeightController@delete')->name('heightDelete');
        });
        Route::group(['prefix' => 'directory_shoe_size'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@create')->name('shoe_sizeCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@createAjax')->name('shoe_sizeCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@list')->name('shoe_sizeList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@edit')->name('shoe_sizeEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@editAjax')->name('shoe_sizeEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ShoeSizeController@delete')->name('shoe_sizeDelete');
        });
        Route::group(['prefix' => 'directory_clothing_size'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@create')->name('clothing_sizeCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@createAjax')->name('clothing_sizeCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@list')->name('clothing_sizeList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@edit')->name('clothing_sizeEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@editAjax')->name('clothing_sizeEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\ClothingSizeController@delete')->name('clothing_sizeDelete');
        });
        Route::group(['prefix' => 'directory_hair_color'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@create')->name('hair_colorCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@createAjax')->name('hair_colorCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@list')->name('hair_colorList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@edit')->name('hair_colorEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@editAjax')->name('hair_colorEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairColorController@delete')->name('hair_colorDelete');
        });
        Route::group(['prefix' => 'directory_hair_length'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@create')->name('hair_lengthCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@createAjax')->name('hair_lengthCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@list')->name('hair_lengthList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@edit')->name('hair_lengthEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@editAjax')->name('hair_lengthEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\HairLengthController@delete')->name('hair_lengthDelete');
        });
        Route::group(['prefix' => 'directory_gender'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@create')->name('genderCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@createAjax')->name('genderCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@list')->name('genderList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@edit')->name('genderEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@editAjax')->name('genderEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\GenderController@delete')->name('genderDelete');
        });
        Route::group(['prefix' => 'directory_messengers'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@create')->name('messengersCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@createAjax')->name('messengersCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@list')->name('messengersList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@edit')->name('messengersEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@editAjax')->name('messengersEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MessengersController@delete')->name('messengersDelete');
        });
        Route::group(['prefix' => 'directory_documentation'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@create')->name('documentationCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@createAjax')->name('documentationCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@list')->name('documentationList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@edit')->name('documentationEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@editAjax')->name('documentationEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\DocumentationController@delete')->name('documentationDelete');
        });
        Route::group(['prefix' => 'directory_organization'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@create')->name('organizationCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@createAjax')->name('organizationCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@list')->name('organizationList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@edit')->name('organizationEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@editAjax')->name('organizationEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\OrganizationController@delete')->name('organizationDelete');
        });
        Route::group(['prefix' => 'directory_age'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@create')->name('ageCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@createAjax')->name('ageCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@list')->name('ageList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@edit')->name('ageEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@editAjax')->name('ageEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\AgeController@delete')->name('ageDelete');
        });

        Route::group(['prefix' => 'directory_medical_book'], function () {
            Route::get('/create/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@create')->name('medical_bookCreate');
            Route::post('/createAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@createAjax')->name('medical_bookCreateAjax');
            Route::get('/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@list')->name('medical_bookList');
            Route::get('/edit/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@edit')->name('medical_bookEdit');
            Route::post('/editAjax/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@editAjax')->name('medical_bookEditAjax');
            Route::get('/delete/{id}/', 'App\Http\Controllers\Admin\Page\Fields\Directory\MedicalBookController@delete')->name('medical_bookDelete');
        });







    });
});
