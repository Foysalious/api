<?php namespace Sheba\EMI;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Constants\UserType;
use GuzzleHttp\Client;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class AccountingDataClient extends ClientRepository
{
    private $api = 'api/entries/emi-entry-list/';
    private $userType = UserType::PARTNER;
    /** @var AccountingEntryClient */
    private $client;
    private $userId;

    public function __construct(Partner $partner)
    {
        $this->client = new AccountingEntryClient(new Client());
        $this->userId = $partner->id;
    }


    /**
     * @return array|mixed
     */
    public function emiList($limit = null)
    {
        try {
            return $this->client->setUserId($this->userId)->setUserType($this->userType)->get($this->api);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return [];
        }
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function getDetailEntry($id)
    {
        try {
            return $this->client->setUserId($this->userId)->setUserType($this->userType)->get($this->api . $id);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }


}