<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Illuminate\Support\Collection;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerRepositoryV2 extends AccountingRepository
{
    private $partner;

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner): DueTrackerRepositoryV2
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function createEntry(array $data)
    {
        return $this->storeEntry((object) $data, $data['source_type']);
    }


    /**
     * @param $query_string
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDuelistBalance($query_string): array
    {
        $url = "api/v2/due-tracker/due-list-balance?".$query_string;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $query_params
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getDueListFromAcc($query_params, $userType = UserType::PARTNER)
    {
         $uri = "api/v2/due-tracker/due-list?" . $query_params;
         try {
            return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($uri);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    /**
     * @param $contact_id
     * @param $url_param
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getDuelistByContactId($contact_id, $url_param, string $userType = UserType::PARTNER)
    {
        $url = "api/v2/due-tracker/due-list/" . $contact_id . "?".$url_param;
        return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $contact_id
     * @param $url_param
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function dueListBalanceByContact($contact_id, $url_param,  string $userType = UserType::PARTNER)
    {
        $url = "api/v2/due-tracker/due-list/" . $contact_id . "/balance?".$url_param;
        return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($url);
    }
    public function reminderByContact($contactId,$contactType){
        //TODO: Update ApI call for Reminder status
        $url = "api/reminders/?contact_type=customer&offset=0&limit=1";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }


}