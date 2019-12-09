<?php namespace App\Http\Route\Prefix\V3;

class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v3', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->get('locations', 'Location\LocationController@index');
            $api->group(['prefix' => 'service-requests'], function ($api) {
                $api->post('/', 'ServiceRequest\ServiceRequestController@store');
            });
        });
    }
}
