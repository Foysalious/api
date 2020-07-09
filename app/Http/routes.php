<?php

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use \Firebase\JWT\JWT;
Route::get('/', function () {
    $team_id = "497KZASBJJ";
    $kid = "FR4RDV4Z5H";
    $client_id = 'xyz.sheba.app';
    $customClaims = [
        'exp' => time() + 86400 * 180,
        'sub' => $client_id,
        'iss' => $team_id,
        'kid' => $kid,
        'aud' => 'https://appleid.apple.com',
        'iat' => time()
    ];
//    $payload = JWTFactory::make($customClaims);
//
////    $token=JWTAuth::setAlgo('ES256');
////    dd($token);
//    $token = JWTAuth::encode($payload);
//    dd($token);


    $payload = array(
        "iss" => $team_id,
        "aud" => 'https://appleid.apple.com',
        "iat" => 1356999524,
        "nbf" => 1357000000,
        'kid' => $kid,
        'sub' => $client_id,
        'exp' => time() + 86400 * 180,
    );

    /**
     * IMPORTANT:
     * You must specify supported algorithms for your application. See
     * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
     * for a list of spec-compliant algorithms.
     */
    $jwt = JWT::encode($payload, $privateKey,'ES256');
    dd($jwt);
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
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
    (new App\Http\Route\Prefix\V1\Route())->set($api);
    (new App\Http\Route\Prefix\V2\Route())->set($api);
    (new App\Http\Route\Prefix\V3\Route())->set($api);
});
