<?php

namespace Sheba\AccountingEntry\Repository;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use App\Sheba\UserMigration\AccountingUserMigration;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Pos\Product\StockToBatchMigration;

class UserMigrationRepository extends BaseRepository
{
    /** @var string */
    private $api = 'api/user-migration/';
    const MODULE_NAME = 'expense';

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
            /** @var StockToBatchMigration $inventoryMigration */
            $inventoryMigration = app(StockToBatchMigration::class);
            $inventoryMigration->setPartnerId($userId)->migrateStock();
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