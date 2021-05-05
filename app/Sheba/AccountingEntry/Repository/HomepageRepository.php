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
}