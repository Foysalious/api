<?php namespace App\Http\Route\Prefix\V2\Partner;

use App\Http\Route\Prefix\V2\Partner\Closed\PartnerClosedRoute;
use App\Http\Route\Prefix\V2\Partner\Open\PartnerOpenRoute;


class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            (new PartnerOpenRoute())->set($api);
            (new PartnerClosedRoute())->set($api);
        });
        $api->post('training-status-update', 'ResourceController@trainingStatusUpdate');
    }
}
