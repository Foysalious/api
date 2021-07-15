<?php namespace App\Http\Route\Prefix\POS\V1;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->get('/channels', "Inventory\ChannelController@index");
            $api->get('/units', "Inventory\UnitController@index");
            $api->group(['prefix' => 'partners/{partner_id}/vouchers'], function ($api) {
                $api->post('validity-check', 'VoucherController@validateVoucher');
            });
        });

        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers', 'middleware' => ['jwtAccessToken']], function ($api) {

            $api->group(['prefix' => 'collections'], function ($api) {
                    $api->get('/', 'Inventory\CollectionController@index');
                    $api->post('/', 'Inventory\CollectionController@store');
                    $api->get('/{collection}', 'Inventory\CollectionController@show');
                    $api->put('/{collection}', 'Inventory\CollectionController@update');
                    $api->delete('/{collection}', 'Inventory\CollectionController@destroy');
                });

                $api->get('warranty-units', 'Inventory\WarrantyUnitController@getWarrantyList');
                $api->get('voucher-details/{voucher_id}', 'VoucherController@getVoucherDetails');

                $api->get('/category-tree', 'Inventory\CategoryController@allCategory');
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
                });

                $api->group(['prefix' => 'products'], function ($api) {
                    $api->get('/', 'Inventory\ProductController@index');
                    $api->post('/', 'Inventory\ProductController@store');
                    $api->group(['prefix' => '{products}'], function ($api) {
                        $api->get('/', 'Inventory\ProductController@show');
                        $api->put('/', 'Inventory\ProductController@update');
                        $api->delete('/', 'Inventory\ProductController@destroy');
                    });
                });
                $api->group(['prefix' => 'category-products'], function ($api) {
                    $api->get('/', 'Inventory\CategoryProductController@getProducts');
                });
                $api->group(['prefix' => 'categories'], function ($api) {
                    $api->get('/', 'Inventory\CategoryController@index');
                    $api->get('/allCategory', 'Inventory\CategoryController@allCategory');
                    $api->post('/', 'Inventory\CategoryController@store');
                    $api->post('/category-with-sub-category', 'Inventory\CategoryController@createCategoryWithSubCategory');
                    $api->put('/{category_id}', 'Inventory\CategoryController@update');
                    $api->delete('/{category_id}', 'Inventory\CategoryController@delete');
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
                    });
                });
                $api->post('migrate', 'Partner\DataMigrationController@migrate');

                $api->group(['prefix' => 'orders'], function ($api) {
                    $api->get('/', 'PosOrder\OrderController@index');
                    $api->get('/{order}', 'PosOrder\OrderController@show');
                    $api->post('/', 'PosOrder\OrderController@store');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->post('/update-status', 'PosOrder\OrderController@updateStatus');
                        $api->post('/validate-promo', 'PosOrder\OrderController@validatePromo');
                    });
                    $api->put('/{order}', 'PosOrder\OrderController@update');
                    $api->delete('/{order}', 'PosOrder\OrderController@destroy');
                });
        });

        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->post('test-migrate', 'Partner\DataMigrationController@testMigration');
        });
    }
}


