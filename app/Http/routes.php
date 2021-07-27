<?php



Route::get('/', function () {
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
    (new App\Http\Route\Prefix\V4\Route())->set($api);
    (new App\Http\Route\Prefix\POS\V1\Route())->set($api);
});



Route::get('/test', function (){

    $data = [
      'sub_domain' => 'my_domain',
      'name' => 'Giga Partner'
    ];
    //update and save
    $id = 38376;
    $partner = \App\Models\Partner::where('id', $id )->first();
//    $partner->sub_domain = 'my_domain51';
    $partner->sub_domain = 'my_domain50'.rand(0,50);
    $partner->name = 'saikat'.rand(0,50);
//    $partner->name = 'saikat5';
    $partner->bkash_no = '017';
    $partner->save();
    $pos_settings = $partner->posSetting;
    $pos_settings->sms_invoice = 0;
    $pos_settings->auto_printing = 1;
    $pos_settings->vat_percentage = (float) rand(20,25)/10;
    $pos_settings->save();

    //created and saved
//    $partner = new \App\Models\Partner(['name' => 'Ben', 'sub_domain' => 'azmoth2']);
//    $partner->save();
    //created and saved
//    $partner = \App\Models\Partner::create(['name' => 'Ben'.rand(5,100), 'sub_domain' => 'CN'.rand(5,100)]);
//    event(new \App\Sheba\InventoryService\Partner\Events\Created($partner));
    dd('end in route');


});
