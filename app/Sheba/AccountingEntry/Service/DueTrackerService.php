<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\Pos\Order\PosOrderObject;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\Pos\Order\PosOrderResolver;
use Sheba\Reports\PdfHandler;
use Exception;


class DueTrackerService
{
    protected $partner;
    protected $dueTrackerRepo;
    protected $contactType;
    protected $order;
    protected $order_by;
    protected $balance_type;
    protected $limit;
    protected $offset;
    protected $query;
    protected $filter_by_supplier;
    protected $amount;
    protected $entry_type;
    protected $account_key;
    protected $customer_id;
    protected $date;
    protected $partner_wise_order_id;
    protected $attachments;
    protected $start_date;
    protected $end_date;


    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    /**
     * @param mixed $amount
     * @return DueTrackerService
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $entry_type
     * @return DueTrackerService
     */
    public function setEntryType($entry_type)
    {
        $this->entry_type = $entry_type;
        return $this;
    }

    /**
     * @param mixed $account_key
     * @return DueTrackerService
     */
    public function setAccountKey($account_key)
    {
        $this->account_key = $account_key;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return DueTrackerService
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $date
     * @return DueTrackerService
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param mixed $partner_wise_order_id
     * @return DueTrackerService
     */
    public function setPartnerWiseOrderId($partner_wise_order_id)
    {
        $this->partner_wise_order_id = $partner_wise_order_id;
        return $this;
    }

    /**
     * @param mixed $attachments
     * @return DueTrackerService
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return DueTrackerService
     */
    public function setPartner($partner): DueTrackerService
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $contactType
     * @return DueTrackerService
     */
    public function setContactType($contactType): DueTrackerService
    {
        $this->contactType = $contactType;
        return $this;
    }

    /**
     * @param mixed $order
     * @return DueTrackerService
     */
    public function setOrder($order): DueTrackerService
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $order_by
     * @return DueTrackerService
     */
    public function setOrderBy($order_by): DueTrackerService
    {
        $this->order_by = $order_by;
        return $this;
    }

    /**
     * @param mixed $balance_type
     * @return DueTrackerService
     */
    public function setBalanceType($balance_type): DueTrackerService
    {
        $this->balance_type = $balance_type;
        return $this;
    }

    /**
     * @param mixed $limit
     * @return DueTrackerService
     */
    public function setLimit($limit): DueTrackerService
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return DueTrackerService
     */
    public function setOffset($offset): DueTrackerService
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $query
     * @return DueTrackerService
     */
    public function setQuery($query): DueTrackerService
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param mixed $filter_by_supplier
     */
    public function setFilterBySupplier($filter_by_supplier): DueTrackerService
    {
        $this->filter_by_supplier = $filter_by_supplier;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance()
    {
        $query_string = $this->generateDueListSearchQueryString();
        return $this->dueTrackerRepo->getDueListBalance($this->partner->id, $query_string);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function searchDueList()
    {
        $query_string = $this->generateDueListSearchQueryString();
        return $this->dueTrackerRepo->searchDueList($this->partner->id, $query_string);
    }

    private function generateDueListSearchQueryString(): string
    {
        $query_strings = [];
        if (isset($this->order_by)) {
            $query_strings [] = 'order_by=' . $this->order_by;
            $query_strings [] = isset($this->order) ? 'order=' . strtolower($this->order) : 'order=desc';
        }

        if (isset($this->balance_type)) {
            $query_strings [] = "balance_type=$this->balance_type&";
        }

        if (isset($this->query)) {
            $query_strings [] = "q=$this->query";
        }

        if (isset($this->limit) && isset($this->offset)) {
            $query_strings [] = "limit=$this->limit";
            $query_strings [] = "offset=$this->offset";
        }

        if (isset($this->contactType)) {
            $query_strings [] = "contact_type=" . strtolower($this->contactType);
        }

        if (isset($this->filter_by_supplier)) {
            $query_strings [] = "filter_by_supplier=" . $this->filter_by_supplier;
        }

        return implode('&', $query_strings);
    }

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        return $this;
    }
    /**
     * @param null $orderId
     * @return PosOrderObject
     */
    private function posOrderByOrderId($orderId)
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setOrderId($orderId)->get();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $request
     * @return string|void
     * @throws AccountingEntryServerError
     * @throws \Mpdf\MpdfException
     * @throws \Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     * @throws \Throwable
     */
    public function downloadPDF($request){

        if($request->customerID == null){
            return $this->dueTrackerRepo->getDuelistPdf($request);
        }
        else return $this->dueTrackerRepo->getDuelistPdfByCustomerId($request);

    }

    /**
     * @param $request
     * @return array|void
     */
    public function dueList($request){
        if($request->customerId == null){
            return $this->dueTrackerRepo->getDuelist($request);
        }
        else{
            $due_list = $this->dueTrackerRepo->getDuelistByCustomerId($request);
            $list = $due_list->map(
                function ($item) {
                    if ($item["attachments"]) {
                        $item["attachments"] = is_array($item["attachments"]) ? $item["attachments"] : json_decode($item["attachments"]);
                    }
                    $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
                    $item['entry_at'] = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
                    $pos_order = $item['source_id'] && $item['source_type'] == EntryTypes::POS ? $this->posOrderByOrderId($item['source_id']): null;
                    $item['partner_wise_order_id'] = isset($pos_order) ? $pos_order->partner_wise_order_id : null;
                    if ($pos_order) {
                        $item['source_type'] = 'PosOrder';
                        $item['head'] = 'POS sales';
                        $item['head_bn'] = 'সেলস';
                        if ($pos_order->sales_channel == SalesChannels::WEBSTORE) {
                            $item['source_type'] = 'Webstore Order';
                            $item['head'] = 'Webstore sales';
                            $item['head_bn'] = 'ওয়েবস্টোর সেলস';
                        }
                    }
                    return $item;
                }
            );
            return [
                'list' => $list
            ];

        }
    }
}