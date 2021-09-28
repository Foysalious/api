<?php

namespace App\Sheba\UserMigration;

use Exception;

class UserMigrationService
{
    public function resolveClass($name)
    {
        if ($name == 'expense') {
            return app(AccountingUserMigration::class);
        }
        if ($name == 'pos') {
            return app(PosUserMigration::class);
        }
        throw new Exception('No Class found!');
    }
}