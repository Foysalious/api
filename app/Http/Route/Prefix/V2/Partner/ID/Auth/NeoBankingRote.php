<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class NeoBankingRote {
    public function set($api) {
        $api->group([
            'prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->group(['prefix' => 'neo-banking'], function ($api) {
                    $api->get('/dashboard', 'NeoBanking\\NeoBankingController@getDashboardData');
                });
        });
    }
}