<?php

use App\Models\Profile;
use Tymon\JWTAuth\Facades\JWTAuth;

Route::get('/', function () {
    $profile = Profile::find(7824);
    $member = $profile->member;
    $businesses = $member->businesses->first();
    return JWTAuth::fromUser($profile, [
        'member_id' => $member->id,
        'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
        'business_id' => $businesses ? $businesses->id : null,
    ]);
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
});
