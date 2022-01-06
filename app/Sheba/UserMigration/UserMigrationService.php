<?php

namespace App\Sheba\UserMigration;

use App\Models\Partner;
use Exception;
use Sheba\Dal\UserMigration\Model as UserMigration;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\ModificationFields;

class UserMigrationService
{
    use ModificationFields;

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

    public function autoMigrate(Partner $partner)
    {
        $payload = [
            ['module_name' => Modules::EXPENSE, 'user_id' => $partner->id, 'status' => UserStatus::UPGRADED],
            ['module_name' => Modules::POS, 'user_id' => $partner->id, 'status' => UserStatus::UPGRADED],
        ];
        UserMigration::create($this->withBothModificationFields($payload));
    }
}