<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class DeliveryRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos/delivery',], function ($api) {
            $api->post('/delivery-charge', 'Pos\\DeliveryController@getDeliveryCharge');
            $api->get('/district', 'Pos\\DeliveryController@getDistrict');
            $api->get('/upzillas/{district_name}/district', 'Pos\\DeliveryController@getUpzilla');
        });
        $api->group(['prefix' => 'pos/delivery', 'middleware' => ['accessToken']], function ($api) {
            $api->get('register', 'Pos\\DeliveryController@getInfoForRegistration');
            $api->post('register', 'Pos\\DeliveryController@register');
            $api->get('/order-information/{order_id}', 'Pos\\DeliveryController@getOrderInformation');
            $api->get('delivery-status', 'Pos\\DeliveryController@getDeliveryStatus');
            $api->post('cancel-order', 'Pos\\DeliveryController@cancelOrder');
            $api->post('orders', 'Pos\\DeliveryController@orderPlace');
            $api->post('partner-vendor', 'Pos\\DeliveryController@partnerVendorUpdate');
        });
    }
}
