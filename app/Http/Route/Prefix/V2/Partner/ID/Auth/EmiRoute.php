<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class EmiRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'emi'], function ($api) {
            $api->get('/home', 'Partner\EmiController@index');
        });
    }
}