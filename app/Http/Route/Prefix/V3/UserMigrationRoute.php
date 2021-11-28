<?php

namespace App\Http\Route\Prefix\V3;

class UserMigrationRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'user-migration/webhook'], function ($api) {
            $api->post('/','UserMigration\UserMigrationController@updateStatusWebHook')->name('migration.update-status-webhook');
        });
        $api->group(['prefix' => 'user-migration', 'middleware' => ['userMigration.auth']], function ($api) {
            $api->get('/module-access/{moduleName}','UserMigration\UserMigrationController@checkModuleAccess')->name('migration.module-access');
            $api->get('/','UserMigration\UserMigrationController@getMigrationList')->name('migration.list');
            $api->get('/{moduleName}','UserMigration\UserMigrationController@migrationStatusByModuleName')->name('migration.get.module');
            $api->post('/{moduleName}','UserMigration\UserMigrationController@updateMigrationStatus')->name('migration.update-status');
        });
    }
}