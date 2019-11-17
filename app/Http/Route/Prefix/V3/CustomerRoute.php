<?php namespace App\Http\Route\Prefix\V3;


use App\Http\Route\Prefix\V2\TransportRoute;

class CustomerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('preferred-partners', 'Customer\CustomerPartnerController@getPreferredPartners');
        });
    }
}
