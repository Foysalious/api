<?php

namespace App\Http\Route\Prefix\V3;

class UserMigrationRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'user-migration', 'middleware' => ['userMigration.auth']], function ($api) {
            $api->get('/','UserMigration\UserMigrationController@getMigrationList');
        });
    }
}