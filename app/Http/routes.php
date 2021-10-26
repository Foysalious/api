<?php

Route::get('/', function () {
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
});

Route::get('/check-instance', function () {
    $instance_id = 'demo_instance_id';
    $ip = request()->ip();
    try {
        $instance_id = file_get_contents("http://instance-data/latest/meta-data/instance-id");
    } catch (Exception $e) {};

    return [
        'code' => 200,
        'message' => "Success. Instance ID: $instance_id & From IP: $ip"
    ];
});

Route::get('/health', function () {
    return ['code' => 200];
});

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
    $api->group(['middleware' => 'bindings'], function ($api) {
        (new App\Http\Route\Prefix\V1\Route())->set($api);
        (new App\Http\Route\Prefix\V2\Route())->set($api);
        (new App\Http\Route\Prefix\V3\Route())->set($api);
        (new App\Http\Route\Prefix\V4\Route())->set($api);
        (new App\Http\Route\Prefix\POS\V1\Route())->set($api);
    });
});
