<?php namespace App\Http\Route\Prefix\V3;

class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v3', 'namespace' => 'App\Http\Controllers'], function ($api) {
            (new CustomerRoute())->set($api);
            (new AffiliateRoute())->set($api);
            (new PartnerRoute())->set($api);
            (new EmiRoute())->set($api);

            $api->group(['middleware' => 'terminate'], function ($api) {
                (new BusinessRoute())->set($api);
            });

            $api->group(['prefix' => 'bank-user', 'middleware' => 'accessToken'], function ($api) {
                $api->get('/information', 'BankUser\BankUserController@getBankUserInfo');
            });

            $api->group(['prefix' => 'retailer-user', 'middleware' => 'accessToken'], function ($api) {
                $api->get('/information', 'StrategicPartner\StrategicPartnerController@getStrategicPartnerInfo');
            });

            $api->get('locations', 'Location\LocationController@index');
            $api->get('thana/reverse', 'Location\LocationController@getThanaFromLatLng');
            $api->get('thanas', 'Thana\ThanaController@index');
            $api->get('times', 'Schedule\ScheduleTimeController@index');
            $api->get('sluggable-type/{slug}', 'ShebaController@getSluggableType');
            $api->post('redirect-url', 'ShebaController@redirectUrl');
            $api->group(['prefix' => 'schema'], function ($api) {
                $api->get('/', 'SchemaController@getAllSchemas');
            });
            $api->group(['prefix' => 'partners'], function ($api) {
                $api->get('send-order-requests', 'Partner\PartnerListController@getPartners');
                $api->get('/', 'Partner\PartnerListController@get');
            });
            $api->group(['prefix' => 'rent-a-car'], function ($api) {
                $api->get('prices', 'RentACar\RentACarController@getPrices');
                $api->get('cars', 'RentACar\RentACarController@getCars');
                $api->get('thana', 'RentACar\RentACarController@getPickupAndDestinationThana');
            });
            $api->group(['prefix' => 'register'], function ($api) {
                $api->post('accountkit', 'AccountKit\AccountKitController@continueWithKit');
            });
            $api->group(['prefix' => 'categories'], function ($api) {
                $api->get('/', 'Category\CategoryController@getMasterCategories');
                $api->get('tree', 'Category\CategoryController@getCategoryTree');
                $api->group(['prefix' => '{category}'], function ($api) {
                    $api->get('/', 'Category\CategoryController@show');
                    $api->get('/sub-categories', 'Category\CategoryController@getSubCategories');
                    $api->get('secondaries', 'Category\CategoryController@getSecondaries');
                    $api->get('services', 'Category\CategoryController@getServicesOfChildren');
                });
            });
            $api->group(['prefix' => 'category-groups'], function ($api) {
                $api->get('/', 'CategoryGroup\CategoryGroupController@index');
            });
            $api->group(['prefix' => 'services'], function ($api) {
                $api->get('suggestions', 'Service\ServiceController@getSuggestions');
                $api->group(['prefix' => '{service}'], function ($api) {
                    $api->get('/', 'Service\ServiceController@show');
                });
            });
            $api->group(['prefix' => 'service-requests'], function ($api) {
                $api->post('/', 'ServiceRequest\ServiceRequestController@store');
            });
            $api->get('training-videos', 'TrainingVideoController@index');
            $api->get('sitemap', 'SitemapController@index');
            $api->get('settings/car', 'HomePageSettingController@getCarV3');
            $api->group(['prefix' => 'subscriptions'], function ($api) {
                $api->get('/{id}', 'SubscriptionController@details');
            });
            $api->get('payment-gateways/{service_type}', 'PaymentGatewayController@getPaymentGateways');
//            emi-info with static info
            $api->get('emi-info', 'ShebaController@getEmiInfoV3');
            $api->group(['prefix' => 'spro', 'middleware' => 'resource.jwt.auth'], function ($api){
                $api->get('service/{serviceId}/instructions', 'Service\ServiceController@instructions')->where('serviceId', '[0-9]+');
            });
        });
    }
}
