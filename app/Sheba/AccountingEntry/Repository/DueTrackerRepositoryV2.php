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
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance($userId, $query_params, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get("api/v2/due-tracker/due-list-balance?" . $query_params);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function searchDueList($userId, $query_params, $userType = UserType::PARTNER)
    {
         $uri = "api/v2/due-tracker/due-list?" . $query_params;
         try {
            return $this->client->setUserType($userType)->setUserId($userId)->get($uri);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    /**
     * @param $request
     * @return string
     * @throws AccountingEntryServerError
     * @throws \Mpdf\MpdfException
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     * @throws \Throwable
     */
//    public function getDuelistPdf($request)
//    {
//
//        $accountingDuetrackerRepository= new AccountingDueTrackerRepository($this->client);
//        $data = $accountingDuetrackerRepository->setPartner($request->partner)->getDueList($request);
//
//        $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
//        $data['end_date']   = $request->has("end_date") ? $request->end_date : null;
//        $balanceData        = $accountingDuetrackerRepository->setPartner($request->partner)->getDuelistBalance($request);
//        $data               = array_merge($data, $balanceData);
//        $pdf_link           = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile(
//            'due_tracker_due_list'
//        )->save(true);
//
//        return $pdf_link;
//
//    }

    /**
     * @param $request
     * @return string
     * @throws AccountingEntryServerError
     * @throws \Mpdf\MpdfException
     * @throws \Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     * @throws \Throwable
     */
    public function getDuelistPdfByCustomerId($request)
    {

        $accountingDuetrackerRepository = new AccountingDueTrackerRepository($this->client);
        $data = $accountingDuetrackerRepository->setPartner($request->partner)->getDueListByCustomer($request, $request->customerID);

        $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
        $data['end_date']   = $request->has("end_date") ? $request->end_date : null;
        $balanceData        = $accountingDuetrackerRepository->setPartner($request->partner)->dueListBalanceByCustomer($request->customerID,$request);
        $data               = array_merge($data, $balanceData);
        $pdf_link           = (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer')->save(true);

       return $pdf_link;

    }

    /**
     * @param $url_pram
     * @param $partner_id
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getDuelist($url_param, $partner_id){
        $url = "api/due-list/?".$url_param;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->get($url);
    }


    /**
     * @param $url_param
     * @param $customer_id
     * @param $partner_id
     * @return \Illuminate\Support\Collection
     * @throws AccountingEntryServerError
     */
    public function getDuelistByCustomerId($url_param, $customer_id, $partner_id){

        $url = "api/due-list/" . $customer_id . "?".$url_param;
        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->get($url);
        return collect($result['list']);

    }
    public function dueListBalanceByCustomer($url_param, $customerId, $partner_id){
        $url = "api/due-list/" . $customerId . "/balance?";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner_id)->get($url);

    }


}