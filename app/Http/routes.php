<?php


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use \Firebase\JWT\JWT;

Route::get('/', function () {

    $client_id = 'xyz.sheba.app';
    $redirect_uri = 'https://api.dev-sheba.xyz/v1/apple';
    $_SESSION['state'] = bin2hex(random_bytes(5));

    $authorize_url = 'https://appleid.apple.com/auth/authorize' . '?' . http_build_query([
            'response_type' => 'code',
            'response_mode' => 'form_post',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'state' => $_SESSION['state'],
            'scope' => 'name email',
        ]);
    echo '<a href="' . $authorize_url . '">Sign In with Apple</a>';
//    (new \Sheba\Apple\ClientSecret())->create();
//
//    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
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
