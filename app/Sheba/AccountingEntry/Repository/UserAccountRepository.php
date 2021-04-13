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
        $this->api = 'api/accounts/';
    }

    public function getAccountType(array $filter = [])
    {
        $query = '';
        if (isset($filter['root_account'])) {
            $query .= "?root_account=" . $filter['root_account'];
        }
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId(auth()->id())->get(
                $this->api . 'account-types' . $query
            );
        } catch (AccountingEntryServerError $e) {
            logError($e);
            return $e->getMessage();
        }
    }
}