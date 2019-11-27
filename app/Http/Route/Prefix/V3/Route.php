<?php namespace App\Http\Route\Prefix\V3;

class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v3', 'namespace' => 'App\Http\Controllers'], function ($api) {
            (new CustomerRoute())->set($api);
            $api->get('locations', 'Location\LocationController@index');
            $api->get('sluggable-type/{slug}', 'ShebaController@getSluggableType');
        });
    }
}
