<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class IndexRoute
{
    public function set($api)
    {
        $api->group(['prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->group(['prefix' => 'order-requests'], function ($api) {
                $api->get('/', 'Partner\OrderRequestController@lists');
                $api->group(['prefix' => '{order-request}'], function ($api) {
                    $api->post('/accept', 'Partner\OrderRequestController@accept');
                    $api->post('/decline', 'Partner\OrderRequestController@decline');
                });
            });
        });
    }
}
