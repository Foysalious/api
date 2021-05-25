<?php namespace App\Http\Route\Prefix\V2\Partner;

class PosRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos'], function ($api) {
            $api->get('units', 'Pos\ServiceController@getUnits');
            $api->get('warranty-units', 'Pos\ServiceController@getWarrantyUnits');
            $api->get('weight-units', 'Pos\ServiceController@getPosProductWeightUnit');
        });
    }
}
