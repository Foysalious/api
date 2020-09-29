<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class PaymentLinkClientRoute
{
    public function set($api)
    {
        $api->group(['prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->group(['prefix' => 'external-payment-links'], function ($api) {
                $api->group(['prefix' => 'clients'], function ($api) {
                    $api->get('/', 'ExternalPaymentLink\\ClientController@index');
                });
            });
        });
    }
}