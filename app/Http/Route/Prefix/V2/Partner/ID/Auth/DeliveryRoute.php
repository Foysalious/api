<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class DeliveryRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'delivery'], function ($api) {
            $api->get('registration', 'Pos\\DeliveryController@getInfoForRegistration');
        });
    }
}