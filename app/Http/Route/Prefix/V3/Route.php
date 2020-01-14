<?php namespace App\Http\Route\Prefix\V3;

class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v3', 'namespace' => 'App\Http\Controllers'], function ($api) {
            (new CustomerRoute())->set($api);
            (new AffiliateRoute())->set($api);
            $api->get('locations', 'Location\LocationController@index');
            $api->get('times', 'Schedule\ScheduleTimeController@index');
            $api->get('sluggable-type/{slug}', 'ShebaController@getSluggableType');
            $api->post('redirect-url', 'ShebaController@redirectUrl');
            $api->post('breadcrumb', 'ShebaController@getBreadcrumb');

            $api->group(['prefix' => 'schema'], function ($api) {
                $api->get('/faq', 'SchemaController@getFaqSchema');
                $api->get('/website', 'SchemaController@getWebsiteSchema');
                $api->get('/organisation', 'SchemaController@getOrganisationSchema');
                $api->get('/review', 'SchemaController@getReviewSchema');
                $api->get('/category', 'SchemaController@getCategorySchema');
            });

            $api->get('partners/send-order-requests', 'Partner\PartnerListController@getPartners');
            $api->group(['prefix' => 'rent-a-car'], function ($api) {
                $api->get('prices', 'RentACar\RentACarController@getPrices');
            });
            $api->group(['prefix' => 'register'], function ($api) {
                $api->post('accountkit', 'AccountKit\AccountKitController@continueWithKit');
            });
            $api->group(['prefix' => 'categories'], function ($api) {
                $api->group(['prefix' => '{category}'], function ($api) {
                    $api->get('/', 'Category\CategoryController@show');
                    $api->get('secondaries', 'Category\CategoryController@getSecondaries');
                });
            });
            $api->group(['prefix' => 'services'], function ($api) {
                $api->group(['prefix' => '{service}'], function ($api) {
                    $api->get('/', 'Service\ServiceController@show');
                });
            });
            $api->group(['prefix' => 'service-requests'], function ($api) {
                $api->post('/', 'ServiceRequest\ServiceRequestController@store');
            });
        });

    }
}
