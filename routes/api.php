<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIntegration;
use App\Http\Middleware\CheckRole;

Route::group(["middleware" => 'throttle:100,10'], function () {
    Route::get('/getUserByHash/','App\Http\Controllers\Form\RegistrationController@getUserByHash')->name('getUserByHash');
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



    Route::middleware([CheckRole::class.':client'])->group( function () {
    });
    Route::middleware([CheckRole::class.':manager'])->group( function () {
    });
    Route::middleware([CheckRole::class.':recruiter'])->group( function () {
    });
    Route::middleware([CheckRole::class.':supervisor'])->group( function () {
    });

    Route::get('/getBrand','App\Http\Controllers\UniversalController@getBrand')->name('getBrand');
    Route::post('/setBrandImg','App\Http\Controllers\UniversalController@setBrandImg')->name('setBrandImg');
    Route::get('/getPlace','App\Http\Controllers\UniversalController@getPlace')->name('getPlace');
    Route::post('/setPlace','App\Http\Controllers\UniversalController@setPlace')->name('setPlace');
    Route::post('/delPlace','App\Http\Controllers\UniversalController@delPlace')->name('delPlace');
    Route::post('/setUserData','App\Http\Controllers\UniversalController@setUserData')->name('setUserData');


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
        Route::post('/deleteRequisite/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@deleteRequisite')->name('deleteRequisite');
        Route::post('/deleteEstate/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@deleteEstate')->name('deleteEstate');

        Route::get('/getFormActivities/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getFormActivities')->name('getFormActivities');
        Route::post('/saveUserFieldsActivities/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@saveUserFieldsActivities')->name('saveUserFieldsActivities');

        Route::get('/getBic/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getBic')->name('getBic');

        Route::get('/getMapField/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@getMapField')->name('getMapField');
        Route::post('/setMapField/', 'App\Http\Controllers\PersonalArea\UserPersonalInfoController@setMapField')->name('setMapField');

        Route::group(['prefix' => 'documents'], function () {
            Route::get('/getDocumentSigned/', 'App\Http\Controllers\PersonalArea\DocumentsController@getDocumentSigned')->name('getDocumentSigned');
            Route::get('/getDocumentArchive/', 'App\Http\Controllers\PersonalArea\DocumentsController@getDocumentArchive')->name('getDocumentArchive');
            Route::get('/getDocumentInquiries/', 'App\Http\Controllers\PersonalArea\DocumentsController@getDocumentInquiries')->name('getDocumentInquiries');
            Route::get('/getDocumentConclude/', 'App\Http\Controllers\PersonalArea\DocumentsController@getDocumentConclude')->name('getDocumentConclude');
            Route::get('/getDocumentTerminate/', 'App\Http\Controllers\PersonalArea\DocumentsController@getDocumentTerminate')->name('getDocumentTerminate');
            Route::post('/setConclude/', 'App\Http\Controllers\PersonalArea\DocumentsController@setConclude')->name('setConclude');
            Route::post('/setTerminate/', 'App\Http\Controllers\PersonalArea\DocumentsController@setTerminate')->name('setTerminate');
            Route::get('/getCompanyAndCertificatesInquiries/', 'App\Http\Controllers\PersonalArea\DocumentsController@getCompanyAndCertificatesInquiries')->name('getCompanyAndCertificatesInquiries');
            Route::post('/requestInquiries/', 'App\Http\Controllers\PersonalArea\DocumentsController@requestInquiries')->name('requestInquiries');
        });

        Route::middleware([CheckRole::class.':client'])->group( function () {
            Route::post('/createOrder','App\Http\Controllers\UserRoles\ClientController@createOrder')->name('createOrder');
            Route::post('/cancelOrder','App\Http\Controllers\UserRoles\ClientController@cancelOrder')->name('cancelOrder');
            Route::post('/sendOrder','App\Http\Controllers\UserRoles\ClientController@sendOrder')->name('sendOrder');
            Route::post('/updateOrder','App\Http\Controllers\UserRoles\ClientController@updateOrder')->name('updateOrder');
            Route::post('/createOrderActivity','App\Http\Controllers\UserRoles\ClientController@createOrderActivity')->name('createOrderActivity');
            Route::post('/deleteOrderActivity','App\Http\Controllers\UserRoles\ClientController@deleteOrderActivity')->name('deleteOrderActivity');
            Route::get('/getViewActivitiesForOrder','App\Http\Controllers\UserRoles\ClientController@getViewActivitiesForOrder')->name('getViewActivitiesForOrder');
        });
        Route::middleware([CheckRole::class.':manager'])->group( function () {
            Route::get('/convertTask','App\Http\Controllers\UserRoles\ManagerController@convertTask')->name('convertTask');
            Route::get('/getSurepvisorData','App\Http\Controllers\UserRoles\ManagerController@getSurepvisorData')->name('getSurepvisorData');
            Route::post('/createTask','App\Http\Controllers\UserRoles\ManagerController@createTask')->name('createTask');//??
            Route::post('/updateTask','App\Http\Controllers\UserRoles\ManagerController@updateTask')->name('updateTask');//??
            Route::post('/createTaskActivity','App\Http\Controllers\UserRoles\ManagerController@createTaskActivity')->name('createTaskActivity');//??
            Route::post('/deleteTaskActivity','App\Http\Controllers\UserRoles\ManagerController@deleteTaskActivity')->name('deleteTaskActivity');//??
            Route::get('/getViewActivitiesForTask','App\Http\Controllers\UserRoles\ManagerController@getViewActivitiesForTask')->name('getViewActivitiesForTask');//??
            Route::post('/instructTask','App\Http\Controllers\UserRoles\ManagerController@instructTask')->name('instructTask');//??
            Route::post('/invoiceTask','App\Http\Controllers\UserRoles\ManagerController@invoiceTask')->name('invoiceTask');//??
            Route::post('/cancelTask','App\Http\Controllers\UserRoles\ManagerController@cancelTask')->name('cancelTask');//??

        });
        Route::middleware([CheckRole::class.':recruiter'])->group( function () {
            Route::get('/request/getRequests','App\Http\Controllers\UserRoles\RecruiterController@getRequests')->name('getRequests');//??
            Route::get('/request/getRequest','App\Http\Controllers\UserRoles\RecruiterController@getRequest')->name('getRequest');//??
            Route::post('/request/acceptRequest','App\Http\Controllers\UserRoles\RecruiterController@acceptRequest')->name('acceptRequest');//??
        });
        Route::middleware([CheckRole::class.':specialist'])->group( function () {
        });
        Route::middleware([CheckRole::class.':supervisor'])->group( function () {
        });
        Route::middleware([CheckRole::class.':admin'])->group( function () {

            //Route::post('/moderation/confirmUserRegister','App\Http\Controllers\UserRoles\ManagerController@confirmUserRegister')->name('confirmUserRegister');

            //Route::post('/convertTask','App\Http\Controllers\UserRoles\ManagerController@convertTask')->name('convertTask');
            //Route::post('/acceptOrder','App\Http\Controllers\UserRoles\ManagerController@acceptOrder')->name('acceptOrderadmin');
           // Route::get('/getViewActivitiesForOrder','App\Http\Controllers\UserRoles\ClientController@getViewActivitiesForOrder')->name('getViewActivitiesForOrder');

            //Route::get('/getPlaceForOrder','App\Http\Controllers\UserRoles\ClientController@getPlace')->name('getPlaceForOrderCreate');



            //Route::post('/createOrder','App\Http\Controllers\UserRoles\ClientController@createOrder')->name('createOrder');
            //Route::get('/getOrders','App\Http\Controllers\UserRoles\ClientController@getOrders')->name('getOrders');
            //Route::post('/cancelOrder','App\Http\Controllers\UserRoles\ClientController@cancelOrder')->name('cancelOrder');
            //Route::post('/sendOrder','App\Http\Controllers\UserRoles\ClientController@sendOrder')->name('sendOrder');
            //Route::post('/updateOrder','App\Http\Controllers\UserRoles\ClientController@updateOrder')->name('updateOrder');



            //Route::post('/getOrders','App\Http\Controllers\UserRoles\SupervisorController@getOrders')->name('getOrdersSupervisor');

//            Route::get('/getBrandImg','App\Http\Controllers\UserRoles\ClientController@getBrandImg')->name('getBrandImgClientAndif');
//            Route::post('/setBrandImg','App\Http\Controllers\UserRoles\ClientController@setBrandImg')->name('setBrandImgClientsvfv');
//
//
//
          //  Route::get('/getPlace','App\Http\Controllers\UserRoles\ClientController@getPlace')->name('getPlaceClient');
          //  Route::post('/setPlace','App\Http\Controllers\UserRoles\ClientController@setPlace')->name('setPlaceClient');
        });

        Route::get('/getOrders','App\Http\Controllers\UniversalController@getOrders')->name('getOrders');
        Route::get('/getOrder','App\Http\Controllers\UniversalController@getOrder')->name('getOrder');
        Route::post('/acceptOrder','App\Http\Controllers\UniversalController@acceptOrder')->name('acceptOrder');
        Route::get('/getTasks','App\Http\Controllers\UniversalController@getTasks')->name('getTasks');
        Route::get('/getTask','App\Http\Controllers\UniversalController@getTask')->name('getTask');

        Route::get('/getViewActivitiesForTask','App\Http\Controllers\UniversalController@getViewActivitiesForTask')->name('getViewActivitiesForTask');
        Route::get('/getPlaceForOrder','App\Http\Controllers\UniversalController@getPlaceForOrder')->name('getPlaceForOrderCreate');

        Route::group(['prefix' => 'moderation'], function () {
            Route::get('/getProject', 'App\Http\Controllers\UniversalController@getProject')->name('getProject');
            Route::post('/setProject', 'App\Http\Controllers\UniversalController@setProject')->name('setProject');
            Route::post('/delProject', 'App\Http\Controllers\UniversalController@delProject')->name('delProject');
            Route::get('/getPlaceModeration', 'App\Http\Controllers\UniversalController@getPlaceModeration')->name('getPlaceModeration');
            Route::post('/setPlaceModeration', 'App\Http\Controllers\UniversalController@setPlaceModeration')->name('setPlaceModeration');
            Route::post('/delPlaceModeration', 'App\Http\Controllers\UniversalController@delPlaceModeration')->name('delPlaceModeration');
            Route::get('/getModerationClient','App\Http\Controllers\UniversalController@getModerationClient')->name('getModerationClient');
            Route::get('/getModerationSingleClient','App\Http\Controllers\UniversalController@getModerationSingleClient')->name('getModerationSingleClient');
            Route::post('/confirmUserRegister','App\Http\Controllers\UniversalController@confirmUserRegister')->name('confirmUserRegister');
            Route::post('/setUserImg','App\Http\Controllers\UniversalController@setUserImg')->name('setUserImg');


            Route::get('/getUserSurepvisorData','App\Http\Controllers\UniversalController@getUserSurepvisorData')->name('getUserSurepvisorData');
            Route::get('/getSurepvisors','App\Http\Controllers\UniversalController@getSurepvisors')->name('getSurepvisors');
            Route::post('/setSurepvisors','App\Http\Controllers\UniversalController@setSurepvisors')->name('setSurepvisors');
            Route::post('/delSurepvisor','App\Http\Controllers\UniversalController@delSurepvisor')->name('delSurepvisor');
        });
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/getFromKey/', 'App\Http\Controllers\Settings\SettingsController@getFromKey')->name('getFromKey');
        Route::get('/getAll/', 'App\Http\Controllers\Settings\SettingsController@getAll')->name('getAll');
    });
});

Route::group(['prefix' => 'integration'], function () {
    Route::middleware([CheckIntegration::class])->group(function () {
        Route::get('/ping/', 'App\Http\Controllers\Integration\IntegrationController@ping')->name('ping');
        Route::post('/updateUserData/', 'App\Http\Controllers\Integration\IntegrationController@updateUserData')->name('updateUserData');
    });
});



