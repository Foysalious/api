<?php

Route::get('/', function () {
    $flow = \Sheba\Dal\TripRequestApprovalFlow\Model::where('business_department_id', 7)->first();
    $approvers = new \Sheba\TripRequestApproval\Approvers();
    $approvers->setApprovalFlow($flow)->setBusiness(\App\Models\Business::find(1))->setRequester(\App\Models\BusinessMember::find(5))->getBusinessMemberIds();
    $flow = new Sheba\Dal\TripRequestApprovalFlow\Model();
    $flow->title = "ad";
    $flow->business_department_id = 7;
    $flow->save();
    $flow->approvers()->sync([1, 4, 5, 6,7]);
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
