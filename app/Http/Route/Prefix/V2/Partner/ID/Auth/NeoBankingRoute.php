<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class NeoBankingRoute {
    public function set($api) {
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->group(['prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->group(['prefix' => 'neo-banking'], function ($api) {
                    $api->get('/information-completion', 'NeoBanking\\NeoBankingController@getAccountInformationCompletion');
                    $api->get('/organization-information', 'NeoBanking\\NeoBankingController@getOrganizationInformation');
                    $api->get('/business-information', 'NeoBanking\\NeoBankingController@getBusinessInformation');
                    $api->get('/homepage', 'NeoBanking\\NeoBankingController@getHomePage');
                    $api->get('/account-details', 'NeoBanking\\NeoBankingController@getAccountDetails');
                    $api->post('/create-transaction', 'NeoBanking\\NeoBankingController@createTransaction');

                });
            });
        });
    }
}