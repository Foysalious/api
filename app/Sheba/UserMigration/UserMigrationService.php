<?php

namespace App\Sheba\UserMigration;

class UserMigrationService
{
    public function resolveClass($name)
    {
        if ($name == 'accounting') {
            return app(AccountingUserMigration::class);
        }
        if ($name == 'pos') {
            return app(PosUserMigration::class);
        }
        throw new \Exception('No Class found!');
    }
}