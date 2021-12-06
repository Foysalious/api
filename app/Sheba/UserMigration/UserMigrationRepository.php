<?php

namespace App\Sheba\UserMigration;

use Illuminate\Support\Facades\Redis;
use Sheba\Dal\UserMigration\Contract;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\ModificationFields;

abstract class UserMigrationRepository
{
    use ModificationFields;
    const NOT_ELIGIBLE = 'not_eligible';

    /** @var Contract */
    private $repo;
    protected $userId;
    protected $moduleName;

    public function __construct(Contract $repo)
    {
        $this->repo = $repo;
    }
    public function setModifierUser($entity)
    {
        $this->setModifier($entity);
        return $this;
    }

    abstract public function getStatusWiseResponse(): array;

    abstract public function updateStatus($status);

    abstract public function getBanner();

    abstract public function versionCodeCheck($appVersion, $modulePayload);

    public function setUserId($userId): UserMigrationRepository
    {
        $this->userId = $userId;
        return $this;
    }

    public function setModuleName($moduleName): UserMigrationRepository
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        $info = $this->repo->builder()->where('user_id', $this->userId)->where('module_name', $this->moduleName)->first();
        if ($info) {
            return $info->status;
        }
        return self::NOT_ELIGIBLE;
    }

    /**
     * @throws Exception
     */
    protected function updateMigrationStatus($status)
    {
        $info = $this->repo->builder()->where('user_id', $this->userId)->where('module_name', $this->moduleName)->first();
        if (!$info) {
            throw new Exception('Sorry! Not Found');
        }
        if ($info->status == UserStatus::UPGRADED) {
            throw new Exception('Sorry! Already Migrated.');
        }
        if ($info->status == UserStatus::UPGRADING && ($status == UserStatus::UPGRADING || $status == UserStatus::PENDING)) {
            throw new Exception('Sorry! Already Migrating.');
        }
        if ($status == UserStatus::UPGRADING) {
            Redis::set("user-migration:$this->userId", "$this->moduleName");
        }
        // Api call will be halt if migration failed.
        if ($status == UserStatus::UPGRADED) {
            Redis::del("user-migration:$this->userId");
        }
        $data = ['status' => $status];

        return $info->update($this->withUpdateModificationField($data));
    }
}