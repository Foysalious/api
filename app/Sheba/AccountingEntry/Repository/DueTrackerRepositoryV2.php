<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\Reports\PdfHandler;


class DueTrackerRepositoryV2 extends AccountingRepository
{
    private $partner;

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner): DueTrackerRepositoryV2
    {
        $this->partner = $partner;
        return $this;
    }

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param $query_string
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDuelistBalance($query_string): array
    {
        $url = "api/due-list/balance?".$query_string;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
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
     * @return \Illuminate\Support\Collection
     * @throws AccountingEntryServerError
     */
    public function getDuelistByContactId($contact_id, $url_param, $userType = UserType::PARTNER){

        $url = "api/due-list/" . $contact_id . "?".$url_param;
        return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $contact_id
     * @param $url_param
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function dueListBalanceByContact($contact_id, $url_param,  $userType = UserType::PARTNER){
        $url = "api/due-list/" . $contact_id . "/balance?".$url_param;
        return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($url);
    }


}