<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class AccountingCustomerRepository extends BaseRepository
{
    private $api = 'api/customers/';

    public function __construct()
    {
        /** @var AccountingEntryClient $client */
        $client = app(AccountingEntryClient::class);
        parent::__construct($client);
    }

    public function getAccountingCustomerDetails($customerId, $userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)->get($this->api . $customerId);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}