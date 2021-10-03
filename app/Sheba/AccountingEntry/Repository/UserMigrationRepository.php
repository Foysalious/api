<?php

namespace Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use App\Sheba\UserMigration\AccountingUserMigration;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class UserMigrationRepository extends BaseRepository
{
    /** @var string */
    private $api = 'api/user-migration/';
    CONST MODULE_NAME = 'expense';

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }
    /**
     * @param $userId
     * @param $status
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function migrateInAccounting($userId, $status, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)->get(
                $this->api . $userId . '?status=' . $status
            );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $userId
     * @param string $userType
     * @return null
     */
    public function userStatus($userId, $userType = UserType::PARTNER)
    {
        /** @var AccountingUserMigration $repo */
        $repo = app(AccountingUserMigration::class);
        return $repo->setUserId($userId)->setModuleName(self::MODULE_NAME)->getStatus();
    }
}