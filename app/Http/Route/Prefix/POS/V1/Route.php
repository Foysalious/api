<?php namespace App\Http\Route\Prefix\POS\V1;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['accessToken']], function ($api) {
                $api->group(['prefix' => 'products'], function ($api) {
                    $api->get('/', 'Inventory\ProductController@index');
                });
                $api->group(['prefix' => 'categories'], function ($api) {
                    $api->get('/', 'Inventory\CategoryController@index');
                    $api->post('/', 'Inventory\CategoryController@store');

                });
            });
        });
    }
}