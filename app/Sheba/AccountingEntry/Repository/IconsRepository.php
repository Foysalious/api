<?php

namespace Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class IconsRepository extends BaseRepository
{
    private $api;

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/icons/';
    }

    public function getIconList($userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)->get(
                $this->api
            );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}