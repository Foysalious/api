<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class BidRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'bids'], function ($api) {
            $api->post('/', 'Partner\BidController@store');

            $api->group(['prefix' => '{bid}'], function ($api) {
                $api->post('/', 'Partner\BidController@takeAction');
            });
            $api->group(['prefix' => '{procurement}'], function ($api) {
                $api->post('convert', 'Partner\ProcurementController@convertToBid');
            });
        });
    }
}