<?php

namespace App\Sheba\UserMigration;

class PosUserMigration extends UserMigrationRepository
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
        return 'upgrading';
    }

    public function getBanner()
    {
        return 'pos-banner';
    }
}