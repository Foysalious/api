<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\EloquentImplementation;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;

abstract class UserMigrationRepository
{
    const NOT_ELIGIBLE = 'not_eligible';

    /** @var EloquentImplementation */
    private $repo;
    protected $userId;
    protected $moduleName;

    public function __construct(EloquentImplementation $repo)
    {
        $this->repo = $repo;
    }

    abstract public function getStatusWiseResponse(): array;

    abstract public function updateStatus($status);

    abstract public function getBanner();

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $info = $this->repo->builder()->where('user_id', $this->userId)->where('module_name', $this->moduleName)->first();
        if ($info) {
            return $info->status;
        }
        return self::NOT_ELIGIBLE;
    }

    protected function updateMigrationStatus($status)
    {
        $info = $this->repo->builder()->where('user_id', $this->userId)->where('module_name', $this->moduleName)->first();
        if (!$info) {
            throw new Exception('Sorry! Not Found');
        }
        if ($info->status == UserStatus::UPGRADED) {
            throw new Exception('Sorry! already migrated.');
        }
        if ($info->status == UserStatus::UPGRADING && $status == UserStatus::UPGRADING) {
            throw new Exception('Sorry! Already Migrating.');
        }
        $info->status = $status;
        return $info->save();
    }
}