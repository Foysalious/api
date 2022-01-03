<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class AccountingCustomerRepository extends BaseRepository
{
    private $api = 'api/customers/';
    private $userId;
    private $userType = UserType::PARTNER;

    public function __construct()
    {
        /** @var AccountingEntryClient $client */
        $client = app(AccountingEntryClient::class);
        parent::__construct($client);
    }

    /**
     * @param mixed $userId
     * @return AccountingCustomerRepository
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param string $userType
     * @return AccountingCustomerRepository
     */
    public function setUserType(string $userType): AccountingCustomerRepository
    {
        $this->userType = $userType;
        return $this;
    }

    public function getAccountingCustomerDetails($customerId)
    {
        return $this->client->setUserType($this->userType)->setUserId($this->userId)->get($this->api . $customerId);
    }

    public function updateCustomer($customerId, array $data)
    {
        return $this->client->setUserType($this->userType)->setUserId($this->userId)->put($this->api . $customerId, $data);
    }
}