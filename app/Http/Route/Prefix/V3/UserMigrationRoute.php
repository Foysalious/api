<?php

namespace App\Http\Route\Prefix\V3;

class UserMigrationRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'user-migration/webhook'], function ($api) {
            $api->post('/','UserMigration\UserMigrationController@updateStatusWebHook');
        });
        $api->group(['prefix' => 'user-migration', 'middleware' => ['userMigration.auth']], function ($api) {
            $api->get('/','UserMigration\UserMigrationController@getMigrationList');
            $api->get('/{moduleName}','UserMigration\UserMigrationController@migrationStatusByModuleName');
            $api->post('/{moduleName}','UserMigration\UserMigrationController@updateMigrationStatus');
        });
    }
}