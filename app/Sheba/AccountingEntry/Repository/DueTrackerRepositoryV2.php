<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerRepositoryV2 extends AccountingRepository
{

    const API_VERSION = 'api/v2/';

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getBalance($userId, $startDate, $endDate, $contact_type, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get(self::API_VERSION . "due-tracker/balance?contact_type=$contact_type&start_date=$startDate&end_date=$endDate");
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

}