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

Route::group(['middleware' => [], 'namespace'=>'Api'], function () {
    Route::post('/driver/signin', 'DriverController@signin');
});

Route::group(['middleware' => ['driver.auth'], 'namespace'=>'Api'], function () {
    Route::post('/driver/signout', 'DriverController@signout');
    Route::post('/driver/resetpwd', 'DriverController@resetpwd');
    Route::post('/driver/locationReport', 'DriverController@locationReport');
    Route::post('/driver/getuiReport', 'DriverController@getuiReport');
    Route::get('/task/executing', 'TaskController@executing');
    Route::get('/task/history', 'TaskController@history');
    Route::get('/task/detail', 'TaskController@detail');
    Route::post('/task/start', 'TaskController@start');
    Route::post('/task/arrived', 'TaskController@arrived');
    Route::post('/task/loading', 'TaskController@loading');
    Route::post('/task/unloading', 'TaskController@unloading');
    Route::post('/task/loadingSuccess', 'TaskController@loadingSuccess');
    Route::post('/task/unloadingSuccess', 'TaskController@unloadingSuccess');
    Route::post('/task/placeSuccess', 'TaskController@placeSuccess');
    Route::post('/task/accept', 'TaskController@accept');
    Route::post('/task/reject', 'TaskController@reject');
    Route::post('/task/goback', 'TaskController@goback');
    Route::post('/task/gothome', 'TaskController@gothome');
    Route::post('/task/empty', 'TaskController@empty');
    Route::post('/task/weight', 'TaskController@weight');
    Route::post('/task/writeoff', 'TaskController@writeoff');
    Route::post('/task/stop', 'TaskController@stop');
    Route::post('/task/pause', 'TaskController@pause');
    Route::post('/task/restart', 'TaskController@restart');
    Route::post('/cost/add', 'CostController@add');
    Route::get('/cost/list', 'CostController@list');
    Route::get('/cost/total', 'CostController@total');
});
