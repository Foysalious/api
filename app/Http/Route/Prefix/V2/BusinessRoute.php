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

                $api->get('/transactions', 'B2b\BusinessTransactionController@index');
                $api->get('/dept-role', 'B2b\CoWorkerController@departmentRole');

                $api->group(['prefix' => 'employees'], function ($api) {
                    $api->post('/', 'B2b\CoWorkerController@store');
                    $api->get('/', 'B2b\CoWorkerController@index');
                    $api->get('/{employee}', 'B2b\CoWorkerController@show');
                });

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

            $api->post('/{member}/drivers', 'B2b\DriverController@store');
            $api->post('/{member}/drivers/{driver}', 'B2b\DriverController@update');


            $api->group(['prefix' => '{member}'], function ($api) {
                $api->group(['prefix' => 'drivers'], function ($api) {
                    $api->get('/', 'B2b\DriverController@driverLists');

                    $api->group(['prefix' => '{driver}'], function ($api) {
                        $api->get('/general-info', 'B2b\DriverController@getDriverGeneralInfo');
                        $api->post('/general-info', 'B2b\DriverController@updateDriverGeneralInfo');

                        $api->get('/license-info', 'B2b\DriverController@getDriverLicenseInfo');
                        $api->post('/license-info', 'B2b\DriverController@updateDriverLicenseInfo');

                        $api->get('/contract-info', 'B2b\DriverController@getDriverContractInfo');
                        $api->post('/contract-info', 'B2b\DriverController@updateDriverContractInfo');

                        $api->get('/experience-info', 'B2b\DriverController@getDriverExperienceInfo');
                        $api->post('/experience-info', 'B2b\DriverController@updateDriverExperienceInfo');

                        $api->get('/documents', 'B2b\DriverController@getDriverDocuments');
                        $api->post('/documents', 'B2b\DriverController@updateDriverDocuments');

                        $api->get('/recent-assignment', 'B2b\DriverController@getDriverRecentAssignment');
                    });
                });
                $api->group(['prefix' => 'trips'], function ($api) {
                    $api->get('/', 'B2b\TripRequestController@getTrips');
                    $api->post('/', 'B2b\TripRequestController@createTrip');
                    $api->group(['prefix' => '{trip}'], function ($api) {
                        $api->get('/', 'B2b\TripRequestController@tripInfo');
                        $api->post('comments', 'B2b\TripRequestController@commentOnTrip');
                    });
                });
                $api->group(['prefix' => 'trip-requests'], function ($api) {
                    $api->get('/', 'B2b\TripRequestController@getTripRequests');
                    $api->post('/', 'B2b\TripRequestController@createTripRequests');
                    $api->group(['prefix' => '{trip_requests}'], function ($api) {
                        $api->get('/', 'B2b\TripRequestController@tripRequestInfo');
                        $api->post('/comments', 'B2b\TripRequestController@commentOnTripRequest');
                    });

                });
            });
        });
    }
}