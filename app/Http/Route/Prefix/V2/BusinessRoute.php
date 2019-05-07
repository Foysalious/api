<?php namespace App\Http\Route\Prefix\V2;


class BusinessRoute
{
    public function set($api)
    {
        $api->post('business/login', 'B2b\LoginController@login');
        $api->post('business/register', 'B2b\RegistrationController@register');

        $api->group(['prefix' => 'businesses', 'middleware' => ['business.auth']], function ($api) {
            $api->group(['prefix' => '{business}'], function ($api) {
                $api->post('/invite', 'B2b\BusinessesController@inviteVendors');
                $api->get('/vendors', 'B2b\BusinessesController@getVendorsList');
                $api->get('/vendors/{vendor}/info', 'B2b\BusinessesController@getVendorInfo');
                $api->post('orders', 'B2b\OrderController@placeOrder');
                $api->post('promotions/add', 'B2b\OrderController@applyPromo');
                $api->group(['prefix' => 'orders'], function ($api) {
                    $api->get('/', 'B2b\OrderController@index');
                    $api->group(['prefix' => '{order}', 'middleware' => ['business_order.auth']], function ($api) {
                        $api->get('/', 'B2b\OrderController@show');
                        $api->get('bills/clear', 'B2b\OrderController@clearBills');
                        $api->get('bills', 'B2b\OrderController@getBills');
                    });
                });
            });
        });
        $api->group(['prefix' => 'members', 'middleware' => ['member.auth']], function ($api) {
            $api->post('/{member}/vehicles', 'B2b\VehiclesController@store');
            $api->post('/{member}/vehicles/{vehicle}', 'B2b\VehiclesController@update');

            $api->get('/{member}/vehicles', 'B2b\VehiclesController@vehicleLists');

            $api->get('/{member}/vehicles/{vehicle}/general-info', 'B2b\VehiclesController@getVehicleGeneralInfo');
            $api->post('/{member}/vehicles/{vehicle}/general-info', 'B2b\VehiclesController@updateVehicleGeneralInfo');

            $api->get('/{member}/vehicles/{vehicle}/registration-info', 'B2b\VehiclesController@getVehicleRegistrationInfo');
            $api->post('/{member}/vehicles/{vehicle}/registration-info', 'B2b\VehiclesController@updateVehicleRegistrationInfo');

            $api->get('/{member}/vehicles/{vehicle}/specs', 'B2b\VehiclesController@getVehicleSpecs');
            $api->post('/{member}/vehicles/{vehicle}/specs', 'B2b\VehiclesController@updateVehicleSpecs');

            $api->get('/{member}/vehicles/{vehicle}/recent-assignment', 'B2b\VehiclesController@getVehicleRecentAssignment');

            $api->post('/{member}/drivers', 'B2b\DriversController@store');
            $api->post('/{member}/drivers/{driver}', 'B2b\DriversController@update');

            $api->get('/{member}/drivers', 'B2b\DriversController@driverLists');

            $api->get('/{member}/drivers/{driver}/general-info', 'B2b\DriversController@getDriverGeneralInfo');
            $api->post('/{member}/drivers/{driver}/general-info', 'B2b\DriversController@updateDriverGeneralInfo');
        });
    }
}