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

    public function getIncomeExpenseEntries($userId, $limit, $nextCursor, $startDate, $endDate, $sourceType, $userType = UserType::PARTNER){
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'income-expense-entries?limit='.$limit.($nextCursor?'&next_cursor='.$nextCursor : '') . ($startDate?'&start_date='.$startDate : '') . ($endDate?'&end_date='.$endDate : '') . ($sourceType?'&source_type='.$sourceType : ''));
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

    public function getAccountListBalance($userId, $startDate, $endDate, $limit, $offset, $rootAccount, $userType = UserType::PARTNER) {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . "account-list-balance?" . ($limit ? "limit={$limit}" : "") . ($offset ? "&offset={$offset}" : "") . ($startDate ? "&start_date={$startDate}" : "") . ($endDate ? "&end_date={$endDate}" : ""). ($rootAccount ? "&root_account={$rootAccount}" : ""));
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getEntriesByAccountKey($accountKey, $userId, $limit,  $nextCursor=null, $userType = UserType::PARTNER) {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'cash-accounts-entries/'.$accountKey.'?limit='.$limit.($nextCursor?'&next_cursor='.$nextCursor : '') );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}