<?php

namespace App\Sheba\UserMigration;

class PosUserMigration extends UserMigrationRepository
{
    public function getBanner()
    {
        return 'pos-banner';
    }

    public function getStatusWiseResponse(): array
    {
        // TODO: Implement getStatusWiseResponse() method.
    }

    public function updateStatus($status)
    {
        // TODO: Implement updateStatus() method.
    }
}