<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class DeliveryRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'delivery'], function ($api) {
            $api->get('register', 'Pos\\DeliveryController@getInfoForRegistration');
            $api->post('register', 'Pos\\DeliveryController@register');
            $api->get('/order-information/{order_id}', 'Pos\\DeliveryController@getOrderInformation');
            $api->get('delivery-status', 'Pos\\DeliveryController@getDeliveryStatus');
            $api->post('cancel-order', 'Pos\\DeliveryController@cancelOrder');
            $api->post('/delivery-charge', 'Pos\\DeliveryController@deliveryCharge');
            $api->post('orders', 'Pos\\DeliveryController@orderPlace');
            $api->get('/district', 'Pos\\DeliveryController@districts');
            $api->get('/upzillas/{district_name}/district', 'Pos\\DeliveryController@upzillas');

        });
    }
}
