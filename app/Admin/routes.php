<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => array_merge(config('admin.route.middleware'), [\App\Admin\Middleware\VerifyCompany::class]),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->get('auto/search', 'AutoController@search');
    $router->get('auto/brand', 'AutoController@brand');
    $router->get('auto/serie', 'AutoController@serie');
    $router->get('auto/style', 'AutoController@style');
    $router->get('place/province', 'PlaceController@province');
    $router->get('place/city', 'PlaceController@city');
    $router->get('place/region', 'PlaceController@region');

    $router->get('route/search', 'RouteController@search');

    $router->get('driver/search', 'DriverController@search');

    $router->resource('order/verify', 'OrderController');
    $router->resource('order/doing', 'OrderController');
    $router->resource('order/history', 'OrderController');
    $router->resource('order/check', 'OrderController');

    $router->resource('route', 'RouteController');
    $router->resource('partner', 'PartnerController');
    $router->resource('order', 'OrderController');
    $router->resource('overdraft', 'OverdraftController');
    $router->resource('driver', 'DriverController');
    $router->resource('auto', 'AutoController');
    $router->resource('fleet', 'FleetController');
    $router->resource('trailer', 'TrailerController');
    $router->resource('card', 'CardController');
    $router->resource('place', 'PlaceController');

    $router->resource('company', 'CompanyController');

});


/*
Route::get('storage/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}',function(){
    \App\Util\ImageRoute::imageStorageRoute();
});
*/