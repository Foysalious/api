<?php namespace App\Http\Route\Prefix\V2;

class BusinessRoute
{
    public function set($api)
    {
        $api->post('business/login', 'B2b\LoginController@login');
        $api->get('business/test-login', 'B2b\LoginController@generateDummyToken')->middleware('admin.auth');
        #$api->post('business/register', 'B2b\RegistrationController@register');
        $api->post('business/register', 'B2b\RegistrationController@registerV2');
        $api->group(['prefix' => 'businesses', 'middleware' => ['business.auth']], function ($api) {
            $api->group(['prefix' => '{business}'], function ($api) {
                $api->get('members', 'B2b\MemberController@index');
                $api->post('/invite', 'B2b\BusinessesController@inviteVendors');

                $api->group(['prefix' => 'vendors'], function ($api) {
                    $api->get('/', 'B2b\BusinessesController@getVendorsList');
                    $api->post('/', 'B2b\VendorController@store');
                    $api->post('/bulk-store', 'B2b\VendorController@bulkStore');
                    $api->group(['prefix' => '{vendor}'], function ($api) {
                        $api->get('/info', 'B2b\BusinessesController@getVendorInfo');
                    });
                });
                $api->group(['prefix' => 'subscription-orders'], function ($api) {
                    $api->post('/', 'B2b\OrderController@placeSubscriptionOrder');
                    $api->get('/', 'B2b\SubscriptionOrderController@index');
                    $api->get('/{order}', 'B2b\SubscriptionOrderController@show');
                    $api->get('/{order}/invoice', 'B2b\SubscriptionOrderController@orderInvoice');
                    $api->group(['prefix' => '{subscription_order}'], function ($api) {
                        $api->get('bills/clear', 'B2b\SubscriptionOrderController@clearPayment');
                    });
                });
                $api->get('/vendors', 'B2b\BusinessesController@getVendorsList');
                $api->get('/vendors/{vendor}/info', 'B2b\BusinessesController@getVendorInfo');
                $api->get('/vendors/{vendor}/resource-info', 'B2b\BusinessesController@getVendorAdminInfo');
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
                    $api->get('/forms', 'B2b\InspectionController@inspectionForms');
                    $api->group(['prefix' => '{inspection}'], function ($api) {
                        $api->get('/', 'B2b\InspectionController@show');
                        $api->get('list', 'B2b\InspectionController@getChildrenInspections');
                        $api->post('/', 'B2b\InspectionController@edit');
                        $api->post('submit', 'B2b\InspectionController@submit');
                        $api->group(['prefix' => 'items'], function ($api) {
                            $api->post('/', 'B2b\InspectionItemController@store');
                            $api->group(['prefix' => '{item}'], function ($api) {
                                $api->post('/', 'B2b\InspectionItemController@edit');
                                $api->post('acknowledge', 'B2b\InspectionItemController@acknowledge');
                                $api->delete('/', 'B2b\InspectionItemController@destroy');
                            });
                        });
                    });
                });
                $api->group(['prefix' => 'procurements'], function ($api) {
                    $api->post('/', 'B2b\ProcurementController@store');
                    $api->group(['prefix' => '{procurement}'], function ($api) {
                        $api->post('invitations', 'B2b\ProcurementController@sendInvitation');
                        $api->post('general', 'B2b\ProcurementController@updateGeneral');
                    });
                    $api->get('/', 'B2b\ProcurementController@index');
                    $api->get('/{procurement}', 'B2b\ProcurementController@show');
                });
                $api->group(['prefix' => 'purchase-requests'], function ($api) {
                    $api->get('/', 'B2b\PurchaseRequestController@index');
                    $api->post('/', 'B2b\PurchaseRequestController@store');

                    $api->group(['prefix' => '{purchase_request}'], function ($api) {
                        $api->get('/', 'B2b\PurchaseRequestController@show');
                        $api->post('/change-status', 'B2b\PurchaseRequestController@changeStatus');
                        $api->post('/member-approval-request', 'B2b\PurchaseRequestController@memberApprovalRequest');
                    });

                    $api->group(['prefix' => 'forms'], function ($api) {
                        $api->get('/', 'B2b\PurchaseRequestController@forms');
                    });
                });
                $api->group(['prefix' => 'fuel-logs'], function ($api) {
                    $api->get('/', 'B2b\FuelLogController@index');
                    $api->post('/', 'B2b\FuelLogController@store');
                    $api->post('{fuel_log}', 'B2b\FuelLogController@store');
                });
                $api->group(['prefix' => 'inspection-items'], function ($api) {
                    $api->get('/', 'B2b\InspectionItemController@index');
                    $api->get('/{item}', 'B2b\InspectionItemController@show');
                });
                $api->group(['prefix' => 'issues'], function ($api) {
                    $api->get('/', 'B2b\IssueController@index');
                    $api->post('/', 'B2b\IssueController@store');
                    $api->get('/{issue}', 'B2b\IssueController@show');
                    $api->post('{issue}/close', 'B2b\IssueController@close');
                    $api->post('/{issue}/attachments', 'B2b\IssueController@storeAttachment');
                    $api->get('/{issue}/attachments', 'B2b\IssueController@getAttachments');
                    $api->post('/{issue}/comments', 'B2b\IssueController@storeComment');
                    $api->get('/{issue}/comments', 'B2b\IssueController@getComments');
                });
                $api->group(['prefix' => 'fuel-logs'], function ($api) {
                    $api->get('/', 'B2b\FuelLogController@index');
                    $api->get('/{log}', 'B2b\FuelLogController@show');
                    $api->post('/{log}/attachments', 'B2b\FuelLogController@storeAttachment');
                    $api->get('/{log}/attachments', 'B2b\FuelLogController@getAttachments');
                    $api->post('/{log}/comments', 'B2b\FuelLogController@storeComment');
                    $api->get('/{log}/comments', 'B2b\FuelLogController@getComments');
                });
            });
        });
        $api->group(['prefix' => 'members', 'middleware' => ['member.auth']], function ($api) {
            $api->group(['prefix' => '{member}'], function ($api) {
                $api->get('info', 'B2b\MemberController@getMemberInfo');
                $api->get('get-business-info', 'B2b\MemberController@getBusinessInfo');
                $api->post('update-business-info', 'B2b\MemberController@updateBusinessInfo');
                $api->post('/attachments', 'B2b\MemberController@storeAttachment');
                $api->get('/attachments', 'B2b\MemberController@getAttachments');
                $api->group(['prefix' => 'vehicles'], function ($api) {
                    $api->post('/', 'B2b\VehiclesController@store');
                    $api->post('/bulk-store', 'B2b\VehiclesController@bulkStore');
                    $api->get('/', 'B2b\VehiclesController@index');
                    $api->group(['prefix' => '{vehicle}'], function ($api) {
                        $api->post('/', 'B2b\VehiclesController@update');
                        $api->get('/general-info', 'B2b\VehiclesController@getVehicleGeneralInfo');
                        $api->post('/general-info', 'B2b\VehiclesController@updateVehicleGeneralInfo');
                        $api->get('/registration-info', 'B2b\VehiclesController@getVehicleRegistrationInfo');
                        $api->post('/registration-info', 'B2b\VehiclesController@updateVehicleRegistrationInfo');
                        $api->get('/specs', 'B2b\VehiclesController@getVehicleSpecs');
                        $api->get('handlers', 'B2b\VehiclesController@getVehicleHandlers');
                        $api->put('/driver', 'B2b\VehiclesController@unTagVehicleDriver');
                        $api->post('/specs', 'B2b\VehiclesController@updateVehicleSpecs');
                        $api->post('/specs', 'B2b\VehiclesController@updateVehicleSpecs');
                        $api->get('/recent-assignment', 'B2b\VehiclesController@getVehicleRecentAssignment');
                    });
                });
                $api->group(['prefix' => 'drivers'], function ($api) {
                    $api->post('/', 'B2b\DriverController@store');
                    $api->post('/bulk-store', 'B2b\DriverController@bulkStore');
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
                $api->group(['prefix' => 'inspections'], function ($api) {
                    $api->get('/', 'B2b\InspectionController@individualInspection');
                });
            });
        });
    }
}