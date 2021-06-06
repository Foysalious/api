<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class HomepageRepository extends BaseRepository
{
    private $api;

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/home/';
    }

    /**
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getAssetBalance($userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'asset-balance');
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $userId
     * @param $startDate
     * @param $endDate
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getIncomeExpenseBalance($userId, $startDate, $endDate, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . "income-expense-balance?start_date=$startDate&end_date=$endDate" );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getIncomeExpenseEntries($userId, $limit, $userType = UserType::PARTNER){
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'income-expense-entries?limit='.$limit );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getDueCollectionBalance($userId, $startDate, $endDate, $userType = UserType::PARTNER){
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . "due-tracker-balance?start_date=$startDate&end_date=$endDate");
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getAccountListBalance($userId, $startDate, $endDate, $limit, $userType = UserType::PARTNER) {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . "account-list-balance?start_date=$startDate&end_date=$endDate&limit=$limit");
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}