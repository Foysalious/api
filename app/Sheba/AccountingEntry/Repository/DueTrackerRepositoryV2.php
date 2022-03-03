<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\Reports\PdfHandler;


class DueTrackerRepositoryV2 extends AccountingRepository
{

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getBalance($userId, $contact_type, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get("api/v2/due-tracker/balance?contact_type=$contact_type");
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
    public function getDuelistData($request)
    {

        $accountingDuetrackerRepository= new AccountingDueTrackerRepository($this->client);
        $data = $accountingDuetrackerRepository->setPartner($request->partner)->getDueList($request);

        $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
        $data['end_date']   = $request->has("end_date") ? $request->end_date : null;
        $balanceData        = $accountingDuetrackerRepository->setPartner($request->partner)->getDuelistBalance($request);
        //dd($balanceData);
        $data               = array_merge($data, $balanceData);
        $pdf_link           = (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile(
            'due_tracker_due_list'
        )->save(true);

        return $pdf_link;

    }

    /**
     * @param $request
     * @return string
     * @throws AccountingEntryServerError
     * @throws \Mpdf\MpdfException
     * @throws \Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     * @throws \Throwable
     */
    public function getDuelistDataByCustomerId($request)
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

}