<?php

namespace App\Sheba\UserMigration;


abstract class UserMigrationStrategy
{
    abstract public function getHeader();

    abstract public function getBody();

    abstract public function getFooter();

    abstract public function getStatus();

    abstract public function getBanner();

}