<?php namespace App\Http\Route\Prefix\V2;


class BusinessRoute
{
    public function set($api)
    {
        $api->post('business/login', 'B2b\LoginController@login');
        $api->post('business/register', 'B2b\RegistrationController@register');

        $api->group(['prefix' => 'businesses', 'middleware' => ['business.auth']], function ($api) {
            $api->group(['prefix' => '{business}'], function ($api) {
                $api->get('members', 'B2b\MemberController@index');
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

            $api->group(['prefix' => '{member}'], function ($api) {
                $api->get('/transactions', 'B2b\TransactionController@index');
                $api->group(['prefix' => 'vehicles'], function ($api) {
                    $api->get('/', 'B2b\VehiclesController@vehicleLists');

                    $api->group(['prefix' => '{vehicle}'], function ($api) {
                        $api->get('/general-info', 'B2b\VehiclesController@getVehicleGeneralInfo');
                        $api->post('/general-info', 'B2b\VehiclesController@updateVehicleGeneralInfo');

                        $api->get('/registration-info', 'B2b\VehiclesController@getVehicleRegistrationInfo');
                        $api->post('/registration-info', 'B2b\VehiclesController@updateVehicleRegistrationInfo');

                        $api->get('/specs', 'B2b\VehiclesController@getVehicleSpecs');
                        $api->post('/specs', 'B2b\VehiclesController@updateVehicleSpecs');

                        $api->get('/recent-assignment', 'B2b\VehiclesController@getVehicleRecentAssignment');
                    });
                });
            });

            $api->post('/{member}/drivers', 'B2b\DriversController@store');
            $api->post('/{member}/drivers/{driver}', 'B2b\DriversController@update');

            $api->get('/{member}/trip-requests', 'B2b\TripRequestController@store');

            $api->group(['prefix' => '{member}'], function ($api) {
                $api->group(['prefix' => 'drivers'], function ($api) {
                    $api->get('/', 'B2b\DriversController@driverLists');

                    $api->group(['prefix' => '{driver}'], function ($api) {
                        $api->get('/general-info', 'B2b\DriversController@getDriverGeneralInfo');
                        $api->post('/general-info', 'B2b\DriversController@updateDriverGeneralInfo');

                        $api->get('/license-info', 'B2b\DriversController@getDriverLicenseInfo');
                        $api->post('/license-info', 'B2b\DriversController@updateDriverLicenseInfo');

                        $api->get('/contract-info', 'B2b\DriversController@getDriverContractInfo');
                        $api->post('/contract-info', 'B2b\DriversController@updateDriverContractInfo');

                        $api->get('/experience-info', 'B2b\DriversController@getDriverExperienceInfo');
                        $api->post('/experience-info', 'B2b\DriversController@updateDriverExperienceInfo');

                        $api->get('/documents', 'B2b\DriversController@getDriverDocuments');
                        $api->post('/documents', 'B2b\DriversController@updateDriverDocuments');

                        $api->get('/recent-assignment', 'B2b\DriversController@getDriverRecentAssignment');
                    });
                });
            });
        });
    }
}