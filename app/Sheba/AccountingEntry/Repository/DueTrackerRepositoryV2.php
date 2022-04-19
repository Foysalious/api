<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;

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
        return $this->storeEntry((object)$data, $data['source_type']);
    }


    /**
     * @param $query_string
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDuelistBalance($query_string): array
    {
        $url = "api/v2/due-tracker/due-list-balance?" . $query_string;
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
        $url = "api/v2/due-tracker/due-list/" . $contact_id . "?" . $url_param;
        return $this->client->setUserType($userType)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $contact_id
     * @param $url_param
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function dueListBalanceByContact($contact_id, $url_param, string $userType = UserType::PARTNER)
    {
        $url = "api/v2/due-tracker/due-list/" . $contact_id . "/balance?" . $url_param;
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
     * @param $partner_id
     * @param $url_param
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function reportForWeb($partner_id, $url_param)
    {
        $url = "api/v2/due-tracker/report/web?" . $url_param;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->get($url);
    }

    /**
     * @param $url_param
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getReportForMobile($url_param)
    {
        $url = "api/v2/due-tracker/report/mobile?" . $url_param;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $queryString
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function downloadPdfByContact($queryString){
        $url = "api/v2/due-tracker/report/pdf?".$queryString;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    public function storeJournalForSmsSending($partner, $transaction)
    {
        (new JournalCreateRepository())->setTypeId($partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Due tracker sms sent charge")
            ->setReference("")
            ->store();
    }

    public function getBulkSmsContactList($url_param)
    {
        $url = "api/v2/due-tracker/bulk-sms-eligible-list?" . $url_param;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    public function getContactBalanceById($contact_id, $contact_type=ContactType::SUPPLIER)
    {
        $url = "api/v2/due-tracker/due-list/" . $contact_id . "/balance?" . "&contact_type={$contact_type}" ;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

}