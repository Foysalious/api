<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\UserStatus;

class AccountingUserMigration extends UserMigrationRepository
{
    public function getBanner()
    {
        return 'accounting-banner';
    }

    public function getStatusWiseResponse()
    {
        $status = $this->getStatus();
        if ($status == UserStatus::PENDING) {
            return $this->getPendingResponse();
        }
    }

    public function updateStatus(array $data)
    {
        // TODO: Implement updateStatus() method.
    }

    private function getPendingResponse()
    {
        
    }
}