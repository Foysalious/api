<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class UserAccountRepository extends BaseRepository
{
    private $api;
    private $name;
    private $name_bn;
    private $root_account;
    private $account_type;
    private $icon;
    private $balanceType;
    private $openingBalance;
    private $editable = true;
    private $deletable = true;
    private $visible = true;

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/accounts/';
    }

    /**
     * @param mixed $name
     * @return UserAccountRepository
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $name_bn
     * @return UserAccountRepository
     */
    public function setNameBn($name_bn)
    {
        $this->name_bn = $name_bn;
        return $this;
    }

    /**
     * @param mixed $root_account
     * @return UserAccountRepository
     */
    public function setRootAccount($root_account)
    {
        $this->root_account = $root_account;
        return $this;
    }

    /**
     * @param mixed $account_type
     * @return UserAccountRepository
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;
        return $this;
    }

    /**
     * @param mixed $icon
     * @return UserAccountRepository
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param mixed $openingBalance
     * @return UserAccountRepository
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
        return $this;
    }

    /**
     * @param mixed $balanceType
     * @return UserAccountRepository
     */
    public function setBalanceType($balanceType)
    {
        $this->balanceType = $balanceType;
        return $this;
    }

    /**
     * @param mixed $editable
     * @return UserAccountRepository
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
        return $this;
    }

    /**
     * @param mixed $deletable
     * @return UserAccountRepository
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;
        return $this;
    }

    /**
     * @param mixed $visible
     * @return UserAccountRepository
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @param $userId
     * @param array $request
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getAccountType($userId, array $request = [], string $userType = UserType::PARTNER)
    {
        $query = '';
        if (isset($request['root_account'])) {
            $query .= "?root_account=" . $request['root_account'];
        }
        return $this->client->setUserType($userType)->setUserId($userId)->get($this->api . 'account-types' . $query);
    }

    /**
     * @param $userId
     * @param array $request
     * @param string $userTYpe
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getAccounts($userId, array $request = [], string $userTYpe = UserType::PARTNER)
    {
        $query = '?';
        if (isset($request['root_account'])) {
            $query .= "root_account=" . $request['root_account'];
        }
        if (isset($request['account_type'])) {
            $query .= "&account_type=" . $request['account_type'];
        }
        return $this->client->setUserType($userTYpe)->setUserId($userId)->get($this->api . $query);
    }

    /**
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getCashAccounts($userId, string $userType = UserType::PARTNER)
    {
        return $this->client->setUserType($userType)->setUserId($userId)->get($this->api . 'cash-accounts');
    }

    /**
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeAccount($userId, string $userType = UserType::PARTNER)
    {
        $payload = $this->makeData();
        return $this->client->setUserType($userType)->setUserId($userId)->post($this->api, $payload);
    }

    public function updateAccount($accountId, $userId, $userType = UserType::PARTNER)
    {
        $payload = $this->makeData();
        return $this->client->setUserType($userType)->setUserId($userId)->put($this->api . $accountId, $payload);
    }

    /**
     * @param $accountId
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteAccount($accountId, $userId, string $userType = UserType::PARTNER)
    {
        return $this->client->setUserType($userType)->setUserId($userId)->delete($this->api . $accountId);
    }

    private function makeData(): array
    {
        if ($this->balanceType == 'negative') {
            $this->openingBalance = 0 - $this->openingBalance;
        }
        $data['name'] = $this->name;
        $data['name_bn'] = $this->name_bn;
        $data['root_account'] = $this->root_account;
        $data['account_type'] = $this->account_type;
        $data['icon'] = $this->icon;
        $data['editable'] = $this->editable;
        $data['deletable'] = $this->deletable;
        $data['visible'] = $this->visible;
        $data['opening_balance'] = $this->openingBalance;
        $data['closing_balance'] = $this->openingBalance;
        return $data;
    }
}