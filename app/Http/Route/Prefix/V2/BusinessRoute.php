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
                $api->get('trips', 'B2b\TripSchedulerController@getList');
                $api->post('promotions/add', 'B2b\OrderController@applyPromo');

                $api->get('/transactions', 'B2b\BusinessTransactionController@index');

                $api->get('/dept-role', 'B2b\CoWorkerController@departmentRole');
                $api->post('/departments', 'B2b\CoWorkerController@addBusinessDepartment');
                $api->get('/departments', 'B2b\CoWorkerController@getBusinessDepartments');
                $api->post('/roles', 'B2b\CoWorkerController@addBusinessRole');

                $api->get('/sms-templates', 'B2b\BusinessSmsTemplateController@index');
                $api->get('/test-sms', 'B2b\BusinessSmsTemplateController@sendSms');
                $api->post('/sms-templates/{sms}', 'B2b\BusinessSmsTemplateController@update');
                $api->get('/sms-templates/{sms}', 'B2b\BusinessSmsTemplateController@show');

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

                $api->group(['prefix' => 'form-templates'], function ($api) {
                    $api->get('/', 'B2b\FormTemplateController@index');
                    $api->post('/', 'B2b\FormTemplateController@store');
                    $api->group(['prefix' => '{form_template}'], function ($api) {
                        $api->get('/', 'B2b\FormTemplateController@get');
                        $api->post('/', 'B2b\FormTemplateController@edit');
                        $api->group(['prefix' => 'items'], function ($api) {
                            $api->post('/', 'B2b\FormTemplateItemController@store');
                            $api->group(['prefix' => '{item}'], function ($api) {
                                $api->post('/', 'B2b\FormTemplateItemController@edit');
                                $api->delete('/', 'B2b\FormTemplateItemController@destroy');
                            });
                        });
                    });
                });

                $api->group(['prefix' => 'inspections'], function ($api) {
                    $api->get('/', 'B2b\InspectionController@index');
                    $api->post('/', 'B2b\InspectionController@store');
                    $api->group(['prefix' => '{inspection}'], function ($api) {
                        $api->get('/', 'B2b\InspectionController@show');
                        $api->post('/', 'B2b\InspectionController@edit');
                        $api->group(['prefix' => '{item}'], function ($api) {
                            $api->post('/', 'B2b\InspectionItemController@edit');
                            $api->delete('/', 'B2b\InspectionItemController@destroy');
                        });
                    });
                });
                $api->group(['prefix' => 'issues'], function ($api) {
                    $api->get('/', 'B2b\InspectionItemIssueController@index');
                    $api->get('/{issue}', 'B2b\InspectionItemIssueController@show');
                });
            });
        });
        $api->group(['prefix' => 'members', 'middleware' => ['member.auth']], function ($api) {
            $api->group(['prefix' => '{member}'], function ($api) {
                $api->group(['prefix' => 'vehicles'], function ($api) {
                    $api->post('/', 'B2b\VehiclesController@store');
                    $api->get('/', 'B2b\VehiclesController@index');
                    $api->group(['prefix' => '{vehicle}'], function ($api) {
                        $api->post('/', 'B2b\VehiclesController@update');
                        $api->get('/general-info', 'B2b\VehiclesController@getVehicleGeneralInfo');
                        $api->post('/general-info', 'B2b\VehiclesController@updateVehicleGeneralInfo');
                        $api->get('/registration-info', 'B2b\VehiclesController@getVehicleRegistrationInfo');
                        $api->post('/registration-info', 'B2b\VehiclesController@updateVehicleRegistrationInfo');
                        $api->get('/specs', 'B2b\VehiclesController@getVehicleSpecs');
                        $api->post('/specs', 'B2b\VehiclesController@updateVehicleSpecs');
                        $api->get('/recent-assignment', 'B2b\VehiclesController@getVehicleRecentAssignment');
                    });
                });
                $api->group(['prefix' => 'drivers'], function ($api) {
                    $api->post('/', 'B2b\DriverController@store');
                    $api->get('/', 'B2b\DriverController@index');
                    $api->group(['prefix' => '{driver}'], function ($api) {
                        $api->post('/', 'B2b\DriverController@update');
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