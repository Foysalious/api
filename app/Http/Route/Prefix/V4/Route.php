<?php namespace App\Http\Route\Prefix\V4;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v4', 'namespace' => 'App\Http\Controllers'], function ($api) {

            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->group(['prefix' => 'products'], function ($api) {
                    $api->get('/', 'Inventory\ProductController@index');
                    $api->post('/', 'Inventory\ProductController@store');
                    $api->group(['prefix' => '{product}'], function ($api) {
                        $api->get('/', 'Inventory\ProductController@show');
                        $api->get('/logs', 'Inventory\ProductController@getLogs');
                        $api->post('/', 'Inventory\ProductController@update');
                        $api->delete('/', 'Inventory\ProductController@destroy');
                        $api->post('/toggle-publish-for-shop', 'Inventory\ProductController@togglePublishForShopStatus');
                    });
                });
                $api->group(['prefix' => 'categories'], function ($api) {
                    $api->get('/', 'Inventory\CategoryController@index');
                    $api->post('/', 'Inventory\CategoryController@store');
                });
            });
        });
    }
}