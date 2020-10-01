<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class ExternalPaymentLinkRoute
{
    public function set($api)
    {
        $api->group(['prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->group(['prefix' => 'external-payment-link'], function ($api) {
                $api->group(['prefix' => 'clients'], function ($api) {
                    $api->get('/', 'ExternalPaymentLink\\ClientController@index');
                    $api->post('/store', 'ExternalPaymentLink\\ClientController@store');
                    $api->post('/{client_id}/generate-secret', "ExternalPaymentLink\\ClientController@clientSecretGenerate");
                    $api->get('/{client_id}/details', "ExternalPaymentLink\\ClientController@show");

                });
            });
        });
    }
}