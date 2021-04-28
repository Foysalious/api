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
    private $editable = true;
    private $deletable = true;
    private $visible = true;

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $name_bn
     */
    public function setNameBn($name_bn)
    {
        $this->name_bn = $name_bn;
        return $this;
    }

    /**
     * @param mixed $root_account
     */
    public function setRootAccount($root_account)
    {
        $this->root_account = $root_account;
        return $this;
    }

    /**
     * @param mixed $account_type
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;
        return $this;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param mixed $editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
        return $this;
    }

    /**
     * @param mixed $deletable
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;
        return $this;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }


    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/accounts/';
    }

    public function getAccountType($userId, array $request = [], $userType = UserType::PARTNER)
    {
        $query = '';
        if (isset($request['root_account'])) {
            $query .= "?root_account=" . $request['root_account'];
        }
        try {
            return $this->client->setUserType($userType)->setUserId($userId)->get(
                $this->api . 'account-types' . $query
            );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getAccounts($userId, array $request = [], $userTYpe = UserType::PARTNER)
    {
        $query = '?';
        if (isset($request['root_account'])) {
            $query .= "root_account=" . $request['root_account'];
        }
        if (isset($request['account_type'])) {
            $query .= "&account_type=" . $request['account_type'];
        }
        try {
            return $this->client->setUserType($userTYpe)->setUserId($userId)->get(
                $this->api . $query
            );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getCashAccounts($userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'cash-accounts');
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function storeAccount($userId, $userType = UserType::PARTNER)
    {
        try {
            $payload = $this->makeData();
            return $this->client->setUserType($userType)->setUserId($userId)
                ->post($this->api, $payload);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function updateAccount($accountId, $userId, $userType = UserType::PARTNER)
    {
        try {
            $payload = $this->makeData();
            return $this->client->setUserType($userType)->setUserId($userId)
                ->put($this->api . $accountId . '/', $payload);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAccount($accountId, $userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->delete($this->api . $accountId . '/');
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    private function makeData()
    {
        $data['name'] = $this->name;
        $data['name_bn'] = $this->name_bn;
        $data['root_account'] = $this->root_account;
        $data['account_type'] = $this->account_type;
        $data['icon'] = $this->icon;
        $data['editable'] = $this->editable;
        $data['deletable'] = $this->deletable;
        $data['visible'] = $this->visible;
        return $data;
    }
}