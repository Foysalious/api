<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\EloquentImplementation;

abstract class UserMigrationRepository
{
    const NOT_ELIGIBLE = 'not_eligible';
    /** @var EloquentImplementation */
    private $repo;
    protected $userId;
    protected $moduleKey;

    public function __construct(EloquentImplementation $repo)
    {
        $this->repo = $repo;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setModuleKey($moduleKey)
    {
        $this->moduleKey = $moduleKey;
        return $this;
    }

    abstract public function getStatusWiseResponse();

    abstract public function updateStatus(array $data);

    abstract public function getBanner();

    /**
     * @return string
     */
    public function getStatus()
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