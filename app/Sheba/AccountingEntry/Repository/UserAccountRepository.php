<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class UserAccountRepository extends BaseRepository
{
    private $api;

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/accounts';
    }

    public function getAccountType(array $request = [])
    {
        $query = '';
        if (isset($request['root_account'])) {
            $query .= "?root_account=" . $request['root_account'];
        }
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request['partner']['id'])->get(
                $this->api . '/account-types' . $query
            );
        } catch (AccountingEntryServerError $e) {
            return $e->getMessage();
        }
    }

    public function getAccounts(array $request = [])
    {
        $query = '?';
        if (isset($request['root_account'])) {
            $query .= "root_account=" . $request['root_account'];
        }
        if (isset($request['account_type'])) {
            $query .= "account_type=" . $request['account_type'];
        }
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request['partner']['id'])->get(
                $this->api . $query
            );
        } catch (AccountingEntryServerError $e) {
            return $e->getMessage();
        }
    }
}