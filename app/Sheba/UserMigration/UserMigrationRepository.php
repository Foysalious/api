<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\EloquentImplementation;

abstract class UserMigrationRepository
{
    const NOT_ELIGIBLE = 'not_eligible';
    /** @var EloquentImplementation */
    private $repo;

    public function __construct(EloquentImplementation $repo)
    {
        $this->repo = $repo;
    }

    abstract public function getHeader();

    abstract public function getBody();

    abstract public function getFooter();

    abstract public function getBanner();

    /**
     * @param $userId
     * @param $moduleName
     * @return string
     */
    public function getStatus($userId ,$moduleName)
    {
        //todo: static data for razoan
        return 'upgrading';
//        $info = $this->repo->builder()->where('user_id', $userId)->where('module_name', $moduleName)->first();
//        if ($info) {
//            return $info->status;
//        }
//        return self::NOT_ELIGIBLE;
    }
}