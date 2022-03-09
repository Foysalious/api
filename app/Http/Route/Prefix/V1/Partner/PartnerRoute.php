<?php namespace App\Http\Route\Prefix\V1\Partner;

use App\Http\Route\Prefix\V1\Partner\ID\Auth\DynamicFormRoute;
use App\Http\Route\Prefix\V1\Partner\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V1\Partner\ID\Auth\ExternalPaymentLinkRoute;
use App\Http\Route\Prefix\V1\Partner\ID\Auth\QRPaymentRoute;
use App\Http\Route\Prefix\V1\Partner\ID\Auth\ResellerPaymentRoute;
use App\Http\Route\Prefix\V1\Partner\ID\NonAuth\IndexRoute as IDNonAuthRoute;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            (new IDNonAuthRoute())->set($api);
            (new IDAuthRoute())->set($api);
            $api->get('search', 'Partner\PartnerPosController@search');
        });

        (new ExternalPaymentLinkRoute())->set($api);
        (new ResellerPaymentRoute())->set($api);
        (new QRPaymentRoute())->set($api);
        (new DynamicFormRoute())->set($api);
    }
}
