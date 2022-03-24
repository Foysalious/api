<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
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

    /**
     * @throws AccountingEntryServerError
     */
    public function getSupplierMonthlyDue($contact_id = Null)
    {
        $start_date = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $end_date = Carbon::now()->lastOfMonth()->format('Y-m-d');
        $url = "api/v2/due-tracker/suppliers/due-amount?" . "start_date=$start_date&" . "end_date=$end_date";
        if ($contact_id) {
            $url .= "&contact_id=$contact_id";
        }
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $contactId
     * @param $contactType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function reminderByContact($contactId,$contactType){
        //
        $url = "api/reminders/contact/".$contactId."?contact_type=".$contactType;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }


}