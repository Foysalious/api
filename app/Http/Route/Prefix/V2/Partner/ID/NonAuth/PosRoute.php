<?php namespace App\Http\Route\Prefix\V2\Partner\ID\NonAuth;

use Dingo\Api\Routing\Router;

class PosRoute
{
    public function set(Router $api)
    {
        $api->group(['prefix' => 'pos'], function ($api) {
            $api->get('products', 'Partner\PartnerPosController@getProducts');
            $api->get('search', 'Partner\PartnerPosController@search');
            $api->get('products/{product}', 'Pos\ServiceController@show');
            $api->post('products/orders', 'Pos\OrderController@store');
            $api->get('orders/{order}/download-invoice-from-webstore', 'Pos\OrderController@downloadInvoiceFromWebStore');
        });
    }
}