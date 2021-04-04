<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;


class Accounting
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting'], function ($api) {
            $api->post('/transfer', 'Accounting\\AccountingController@storeAccountsTransfer');
        });
    }
}