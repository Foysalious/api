<?php namespace App\Http\Route\Prefix\V3;

class EmiRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->group(['prefix' => 'emi'], function ($api) {
                $api->get('/home', 'Partner\EmiController@indexV3');
            });
        });
    }
}