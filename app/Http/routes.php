<?php

Route::get('/', function () {
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
});

Route::get('/test', 'TestController@test');
Route::get('/test-push-notification-1', 'TestController@testPushNotification1');
Route::get('/test-push-notification-2', 'TestController@testPushNotification2');
Route::get('/test-push-notification-3', 'TestController@testPushNotification3');

$api = app('Dingo\Api\Routing\Router');

/*
|--------------------------------------------------------------------------
| Version Reminder
|--------------------------------------------------------------------------
|
| When next version comes add a prefix to the old version
| routes and change API_PREFIX in api.php file to null
|
|
*/

$api->version('v1', function ($api) {
    (new App\Http\Route\Prefix\V1\Route())->set($api);
    (new App\Http\Route\Prefix\V2\Route())->set($api);
    (new App\Http\Route\Prefix\V3\Route())->set($api);
});
