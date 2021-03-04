<?php namespace App\Http\Route\Prefix\POS\V1;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers', 'middleware' => ['accessToken']], function ($api) {

            $api->get('/channels', "Inventory\ChannelController@index");
            $api->get('/allCategory', 'Inventory\CategoryController@allCategory');
            $api->group(['prefix' => 'products'], function ($api) {
                $api->get('/', 'Inventory\ProductController@index');
                $api->post('/', 'Inventory\ProductController@store');
                $api->group(['prefix' => '{products}'], function ($api) {
                    $api->get('/', 'Inventory\ProductController@show');
                    $api->put('/', 'Inventory\ProductController@update');
                    $api->delete('/', 'Inventory\ProductController@destroy');
                });
            });
            $api->group(['prefix' => 'categories'], function ($api) {
                $api->get('/', 'Inventory\CategoryController@index');
                $api->get('/units', "Inventory\UnitController@index");
                $api->delete('/value1/{valueId}', "Inventory\ValueController@destroy");


                $api->group(['prefix' => 'partners', 'middleware' => ['accessToken']], function ($api) {
                    $api->group(['prefix' => 'products'], function ($api) {
                        $api->get('/', 'Inventory\ProductController@index');
                        $api->post('/', 'Inventory\ProductController@store');
                        $api->group(['prefix' => '{products}'], function ($api) {
                            $api->get('/', 'Inventory\ProductController@show');
                            $api->put('/', 'Inventory\ProductController@update');
                            $api->delete('/', 'Inventory\ProductController@destroy');
                        });
                    });
                    $api->group(['prefix' => 'categories'], function ($api) {
                        $api->get('/', 'Inventory\CategoryController@index');

                        $api->post('/', 'Inventory\CategoryController@store');
                        $api->put('/{category_id}', 'Inventory\CategoryController@update');
                        $api->delete('/{category_id}', 'Inventory\CategoryController@delete');
                    });
                    $api->group(['prefix' => 'units'], function ($api) {
                        $api->get('/', "Inventory\UnitController@index");
                    });
                    $api->group(['prefix' => 'options'], function ($api) {
                        $api->get('/', "Inventory\OptionController@index");
                        $api->post('/', "Inventory\OptionController@store");
                        $api->group(['prefix' => '{options}'], function ($api) {
                            $api->put('/', "Inventory\OptionController@update");
                            $api->post('values', "Inventory\ValueController@store");
                        });
                    });
                    $api->group(['prefix' => 'values'], function ($api) {
                        $api->group(['prefix' => '{values}'], function ($api) {
                            $api->put('/', "Inventory\ValueController@update");
                            $api->post('/', 'Inventory\CategoryController@store');
                            $api->post('/{category_id}', 'Inventory\CategoryController@update');
                            $api->delete('/{category_id}', 'Inventory\CategoryController@delete');
                        });
//                $api->group(['prefix' => 'units'], function ($api) {
//                    $api->get('/', "Inventory\UnitController@index");
//                });
                        $api->group(['prefix' => 'options'], function ($api) {
                            $api->get('/', "Inventory\OptionController@index");
                            $api->post('/', "Inventory\OptionController@store");
                            $api->delete('/{optionId}', "Inventory\OptionController@destroy");

                            $api->group(['prefix' => '{options}'], function ($api) {
                                $api->put('/', "Inventory\OptionController@update");
                                $api->post('values', "Inventory\ValueController@store");
                            });
                        });
                        $api->group(['prefix' => 'values'], function ($api) {
                            $api->group(['prefix' => '{values}'], function ($api) {
                                $api->put('/', "Inventory\ValueController@update");
                            });
                        });
                    });
                    $api->group(['prefix' => 'collections'], function ($api) {
                        $api->get('/', 'Inventory\CollectionController@index');
                    });
                });
            });
        }

