<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\EloquentImplementation;

abstract class UserMigrationRepository
{
    /** @var EloquentImplementation */
    private $repo;

    public function __construct()
    {
    }

    abstract public function getHeader();

    abstract public function getBody();

    abstract public function getFooter();

    abstract public function getBanner();

    public function getStatus($moduleName)
    {

    }

}