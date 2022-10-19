<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::post('login', 'AuthController@login');
//Route::post('register', 'AuthController@register');

Route::prefix('1.0')->group(function () {


    Route::middleware('auth:api')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Service Routes
        |--------------------------------------------------------------------------
        |
        | All routes relate to services
        |
        */
        Route::get('service/{appId?}', 'ServiceController@all');
        Route::post('service', 'ServiceController@add');
        Route::delete('service/{appId}', 'ServiceController@remove');
        Route::get('service/{appId}/{event}/calls/{status?}/{limit?}', 'ServiceCallController@all');
        Route::post('service/call/retry', 'ServiceCallController@retry');
        Route::get('service/call/body/{search}', 'ServiceCallController@body');


        /*
        |--------------------------------------------------------------------------
        | Thread Routes
        |--------------------------------------------------------------------------
        |
        | All routes relate to Threads
        |
        */
        Route::get('thread', 'ThreadController@list');
        Route::delete('thread/{id}', 'ThreadController@kill');


        /*
        |--------------------------------------------------------------------------
        | Order Routes
        |--------------------------------------------------------------------------
        |
        | All routes relate to Orders
        |
        */
        Route::get('order/{status?}', 'OrderController');


        /*
        |--------------------------------------------------------------------------
        | Fulfillment Routes
        |--------------------------------------------------------------------------
        |
        | All routes relate to Orders
        |
        */
        Route::post('fulfillment/run', 'FulfillmentController@run');
    });
});
