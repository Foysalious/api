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
    public function getDuelistPdf($request)
    {

        $accountingDuetrackerRepository= new AccountingDueTrackerRepository($this->client);
        $data = $accountingDuetrackerRepository->setPartner($request->partner)->getDueList($request);

        $data['start_date'] = $request->has("start_date") ? $request->start_date : null;
        $data['end_date']   = $request->has("end_date") ? $request->end_date : null;
        $balanceData        = $accountingDuetrackerRepository->setPartner($request->partner)->getDuelistBalance($request);
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

    public function getDuelist($request){
        $url = "api/due-list/?";
        $url = $this->updateRequestParam($request, $url);
        return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url);
    }

    /**
     * @param $request
     * @return \Illuminate\Support\Collection
     * @throws AccountingEntryServerError
     */
    public function getDuelistByCustomerId($request){

        $url = "api/due-list/" . $request->customerId . "?";
        $url = $this->updateRequestParam($request, $url);

        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url);
        $due_list = collect($result['list']);
        return $due_list;
    }

    /**
     * @param $request
     * @param string $url
     * @return string
     */
    private function updateRequestParam($request, string $url): string
    {
        $order_by = $request->order_by;
        if (!empty($order_by)) {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url .= "&order_by=$order_by&order=$order";
        }

        if ($request->has('balance_type')) {
            $url .= "&balance_type=$request->balance_type";
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start_date=$request->start_date&end_date=$request->end_date";
        }

        if (($request->has('download_pdf')) && ($request->download_pdf == 1) ||
            ($request->has('share_pdf')) && ($request->share_pdf == 1)) {
            return $url;
        }

        if ($request->has('filter_by_supplier') && $request->filter_by_supplier == 1) {
            $url .= "&filter_by_supplier=$request->filter_by_supplier";
        }

        if ($request->has('q')) {
            $url .= "&q=$request->q";
        }

        if ($request->has('limit') && $request->has('offset')) {
            $url .= "&limit=$request->limit&offset=$request->offset";
        }
        return $url;
    }
}