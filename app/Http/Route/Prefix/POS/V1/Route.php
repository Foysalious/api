<?php namespace App\Http\Route\Prefix\POS\V1;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos/v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
            /**
             * No Middleware
             */
            $api->get('/channels', "Inventory\ChannelController@index");
            $api->get('/units', "Inventory\UnitController@index");
            $api->get('warranty-units', 'Inventory\WarrantyUnitController@getWarrantyList');
            $api->get('/weight-units', "Inventory\UnitController@weightUnits");
            $api->group(['prefix' => 'partners/{partner_id}'], function ($api) {
                $api->post('vouchers/validity-check', 'VoucherController@validateVoucher');
            });
            $api->post('/reward/action', 'PosRebuild\RewardController@actionReward');
            $api->post('/usages', 'PosRebuild\UsageController@store');
            $api->post('/check-access', 'PosRebuild\AccessManagerController@checkAccess');
            $api->get('voucher-details/{voucher_id}', 'VoucherController@getVoucherDetails');
            $api->post('test-migrate', 'Partner\DataMigrationController@testMigration');
            $api->get('emi-calculate', 'PosOrder\OrderController@calculateEmiCharges');
            $api->group(['prefix' => 'partners'], function ($api) {
                $api->group(['prefix' => '{partner}'], function ($api) {
                    $api->get('/', 'PartnerController@findById')->middleware('ip.whitelist');
                    $api->group(['prefix' => 'orders'], function ($api) {
                        $api->group(['prefix' => '{order}'], function ($api) {
                            $api->post('online-payment', 'PosOrder\OrderController@onlinePayment');
                            $api->post('payment-link-created', 'PosOrder\OrderController@paymentLinkCreated');
                        });
                    });
                });
            });
            /**
             * End of No Middleware
             */

            /**
             * IP Whitelist Middleware
             */
            $api->group(['middleware' => ['ip.whitelist']], function ($api) {
                $api->post('send-sms', "PosRebuild\SmsController@sendSms");
            });
            /**
             * End of IP Whitelist Middleware
             */

            /**
             * jwtAccessToken Middleware
             */
            $api->group(['middleware' => ['jwtAccessToken']], function ($api) {
                $api->get('/orders/{order_id}/generate-invoice', 'PosOrder\OrderController@orderInvoiceDownload');
                $api->group(['prefix' => 'webstore-theme-settings', 'middleware' => ['jwtAccessToken']], function ($api) {
                    $api->get('/partner-settings', 'WebstoreSettingController@index');
                    $api->get('/theme-details', 'WebstoreSettingController@getThemeDetails');
                    $api->post('/', 'WebstoreSettingController@store');
                    $api->put('/', 'WebstoreSettingController@update');
                });
                $api->group(['prefix' => 'collections'], function ($api) {
                    $api->get('/', 'Inventory\CollectionController@index');
                    $api->post('/', 'Inventory\CollectionController@store');
                    $api->get('/{collection}', 'Inventory\CollectionController@show');
                    $api->put('/{collection}', 'Inventory\CollectionController@update');
                    $api->delete('/{collection}', 'Inventory\CollectionController@destroy');
                });
                $api->group(['prefix' => 'customers'], function ($api) {
                    $api->get('/{customer_id}', 'PosCustomer\PosCustomerController@show');
                    $api->get('/', 'PosCustomer\PosCustomerController@showCustomerByPartnerId');
                    $api->post('/', 'PosCustomer\PosCustomerController@storePosCustomer');
                    $api->put('/{customer_id}', 'PosCustomer\PosCustomerController@updatePosCustomer');
                    $api->get('/{customer_id}/orders', 'PosCustomer\PosCustomerController@orders');
                    $api->delete('/{customer_id}', 'PosCustomer\PosCustomerController@delete');
                });
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
                });
                $api->group(['prefix' => 'products'], function ($api) {
                    $api->get('/', 'Inventory\ProductController@index');
                    $api->post('/', 'Inventory\ProductController@store');
                    $api->group(['prefix' => '{products}'], function ($api) {
                        $api->get('/', 'Inventory\ProductController@show');
                        $api->put('/', 'Inventory\ProductController@update');
                        $api->delete('/', 'Inventory\ProductController@destroy');
                        $api->get('/logs', 'Inventory\ProductController@getLogs');
                        $api->post('/add-stock', 'Inventory\ProductController@addStock');
                    });
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
                    $api->post('/', 'PosOrder\OrderController@store');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('/', 'PosOrder\OrderController@show');
                        $api->post('/update-status', 'PosOrder\OrderController@updateStatus');
                        $api->post('/validate-promo', 'PosOrder\OrderController@validatePromo');
                        $api->get('/logs', 'PosOrder\OrderController@logs');
                        $api->post('payment/create', "Pos\OrderController@createPayment");
                    });
                    $api->put('/{order}/update-customer', 'PosOrder\OrderController@updateCustomer');
                    $api->put('/{order}', 'PosOrder\OrderController@update');
                    $api->delete('/{order}', 'PosOrder\OrderController@destroy');
                });

                /**
                 * Old APIs with jwtAccessToken Middleware
                 */
                $api->get('webstore-settings', 'Partner\Webstore\WebstoreSettingsController@index');
                $api->post('webstore-settings', 'Partner\Webstore\WebstoreSettingsController@update');
                $api->get('webstore-dashboard', 'Partner\Webstore\WebstoreDashboardController@getDashboard');
                $api->post('toggle-webstore-sms-activation', 'PartnerController@toggleSmsActivation');
                $api->get('webstore/banner-list', 'Partner\Webstore\WebstoreSettingsController@bannerList');
                $api->post('webstore/store-banner', 'Partner\Webstore\WebstoreSettingsController@storeBanner');
                $api->post('webstore/update-banner', 'Partner\Webstore\WebstoreSettingsController@updateBanner');
                $api->get('/settings', 'Pos\SettingController@getSettings');
                $api->post('/settings', 'Pos\SettingController@storePosSetting');
                $api->get('settings/printer', 'Pos\SettingController@getPrinterSettings');
                $api->post('vat-registration-number', 'PartnerController@addVatRegistrationNumber');
                $api->post('change-logo', 'PartnerController@changeLogo');
                $api->get('slider-details-and-account-types', 'PartnerController@getSliderDetailsAndAccountTypes');
                $api->get('qr-code', 'PartnerController@getQRCode');
                $api->post('qr-code', 'PartnerController@setQRCode');
                $api->get('orders/{order}/send-sms', 'Pos\OrderController@sendSms');
                $api->get('orders/{order}/send-email', 'Pos\OrderController@sendEmail');
                /**
                 * End Old APIs with jwtAccessToken Middleware
                 */
            });
            /**
             * End jwtAccessToken Middleware
             */
        });
    }
}


