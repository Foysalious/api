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
                    $api->post('/{category_id}', 'Inventory\CategoryController@update');

                });
                $api->group(['prefix' => 'units'], function ($api) {
                    $api->get('/', "Inventory\UnitController@index");
                });
                $api->group(['prefix' => 'options'], function ($api) {
                    $api->get('/', "Inventory\OptionController@index");
                    $api->post('/', "Inventory\OptionController@store");
                    $api->group(['prefix' => '{options}'], function ($api) {
                        $api->put('/', "Inventory\OptionController@update");
                    });
                });
            });
        });
    }
}