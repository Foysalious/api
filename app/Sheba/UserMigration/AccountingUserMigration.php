<?php

namespace App\Sheba\UserMigration;


class AccountingUserMigration extends UserMigrationRepository
{

    public function getHeader()
    {
        // TODO: Implement getHeader() method.
    }

    public function getBody()
    {
        // TODO: Implement getBody() method.
    }

    public function getFooter()
    {
        // TODO: Implement getFooter() method.
    }

    public function getStatus()
    {
        return 'pending';
    }

    public function getBanner()
    {
        return 'accounting-banner';
    }
}