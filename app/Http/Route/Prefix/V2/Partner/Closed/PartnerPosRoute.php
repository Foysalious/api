<?php namespace App\Http\Route\Prefix\V2\Partner\Closed;


class PartnerPosRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos'], function ($api) {
            $api->group(['prefix' => 'categories'], function ($api) {
                $api->get('/', 'Pos\CategoryController@index');
                $api->get('/master', 'Pos\CategoryController@getMasterCategoriesWithSubCategory');
            });
            $api->group(['prefix' => 'services'], function ($api) {
                $api->get('/', 'Pos\ServiceController@index');
                $api->post('/', 'Pos\ServiceController@store');
                $api->group(['prefix' => '{service}'], function ($api) {
                    $api->get('/', 'Pos\ServiceController@show');
                    $api->post('/', 'Pos\ServiceController@update');
                    $api->delete('/', 'Pos\ServiceController@destroy');
                });
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('/', 'Pos\OrderController@index');
                $api->post('/', 'Pos\OrderController@store');
                $api->post('/quick-store', 'Pos\OrderController@quickStore');
                $api->group(['prefix' => '{order}'], function ($api) {
                    $api->get('/', 'Pos\OrderController@show');
                    $api->post('/', 'Pos\OrderController@update');
                    $api->post('/collect-payment', 'Pos\OrderController@collectPayment');
                    $api->get('/send-sms', 'Pos\OrderController@sendSms');
                    $api->get('/send-email', 'Pos\OrderController@sendEmail');
                });
            });
            $api->resources(['customers' => 'Pos\CustomerController']);
            $api->get('settings', 'Pos\SettingController@getSettings');
        });
    }
}