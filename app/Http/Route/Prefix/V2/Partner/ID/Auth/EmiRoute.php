<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class EmiRoute {
    public function set($api) {
        $api->group([
            'prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->group(['prefix' => 'emi'], function ($api) {
                    $api->get('/', 'Partner\EmiController@emiList');
                    $api->get('/home', 'Partner\EmiController@index');
                    $api->get('/{id}', 'Partner\EmiController@details');
                });
            });
    }
}
