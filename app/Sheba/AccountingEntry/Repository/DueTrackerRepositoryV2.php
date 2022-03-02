<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerRepositoryV2 extends AccountingRepository
{

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance($userId, $query_params, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get("api/v2/due-tracker/due-list-balance?" . $query_params);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function searchDueList($userId, $query_params, $userType = UserType::PARTNER)
    {
         $uri = "api/v2/due-tracker/due-list?" . $query_params;
         try {
            return $this->client->setUserType($userType)->setUserId($userId)->get($uri);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

}