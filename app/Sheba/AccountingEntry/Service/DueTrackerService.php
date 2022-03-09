<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\Pos\Order\PosOrderObject;
use App\Sheba\PosOrderService\Services\OrderService as OrderServiceAlias;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Pos\Customer\PosCustomerResolver;
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
    protected $contactId;

    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    /**
     * @param mixed $amount
     * @return DueTrackerService
     */
    public function setAmount($amount): DueTrackerService
    {
        $this->amount = $amount;
        return $this;
    }

    public function setContactId($contactId): DueTrackerService {
        $this->contactId = $contactId;
        return $this;
    }

    /**
     * @param mixed $entry_type
     * @return DueTrackerService
     */
    public function setEntryType($entry_type): DueTrackerService
    {
        $this->entry_type = $entry_type;
        return $this;
    }

    /**
     * @param mixed $account_key
     * @return DueTrackerService
     */
    public function setAccountKey($account_key): DueTrackerService
    {
        $this->account_key = $account_key;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return DueTrackerService
     */
    public function setCustomerId($customer_id): DueTrackerService
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $date
     * @return DueTrackerService
     */
    public function setDate($date): DueTrackerService
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param mixed $partner_wise_order_id
     * @return DueTrackerService
     */
    public function setPartnerWiseOrderId($partner_wise_order_id): DueTrackerService
    {
        $this->partner_wise_order_id = $partner_wise_order_id;
        return $this;
    }

    /**
     * @param mixed $attachments
     * @return DueTrackerService
     */
    public function setAttachments($attachments): DueTrackerService
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
     * @param $start_date
     * @return $this
     */
    public function setStartDate($start_date): DueTrackerService
    {
        $this->start_date = $start_date;
        return $this;
    }

    /**
     * @param $end_date
     * @return $this
     */
    public function setEndDate($end_date): DueTrackerService
    {
        $this->end_date = $end_date;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDueListBalance($queryString);
        return [
            'total_transactions' => $result['total_transactions'],
            'total' => $result['total'],
            'stats' => $result['stats'],
            'partner' => $this->getPartnerInfo($this->partner),
        ];
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueList()
    {
        $queryString = $this->generateQueryString();
        return $this->dueTrackerRepo->setPartner($this->partner)->getDueListFromAcc($queryString);

    }

    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function dueListBalanceByContact(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->dueListBalanceByContact($this->customer_id, $queryString);

        $customer = [];

        if (is_null($result['customer'])) {
            /** @var PosCustomerResolver $posCustomerResolver */
            $posCustomerResolver = app(PosCustomerResolver::class);
            $posCustomer = $posCustomerResolver->setCustomerId($this->customer_id)->setPartner($this->partner)->get();
            if (empty($posCustomer)) {
                throw new InvalidPartnerPosCustomer();
            }
            $customer['id'] = $posCustomer->id;
            $customer['name'] = $posCustomer->name;
            $customer['mobile'] = $posCustomer->mobile;
            $customer['avatar'] = $posCustomer->pro_pic;
            $customer['due_date_reminder'] = null;
            $customer['is_supplier'] = $posCustomer->is_supplier;
        } else {
            $customer['id'] = $result['customer']['id'];
            $customer['name'] = $result['customer']['name'];
            $customer['mobile'] = $result['customer']['mobile'];
            $customer['avatar'] = $result['customer']['proPic'];
            $customer['due_date_reminder'] = $result['customer']['dueDateReminder'];
            $customer['is_supplier'] = $result['customer']['isSupplier'] ? 1 : 0;
        }

        $total_debit = $result['other_info']['total_debit'];
        $total_credit = $result['other_info']['total_credit'];
        $result['balance']['color'] = $total_debit > $total_credit ? '#219653' : '#DC1E1E';
        return [
            'customer' => $customer,
            'partner' => $this->getPartnerInfo($this->partner),
            'stats' => $result['stats'],
            'other_info' => $result['other_info'],
            'balance' => $result['balance']
        ];
    }

//    TODO: Add contact type
    /**
     * @return array
     * @throws AccountingEntryServerError
     * @throws \App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError
     */
    public function dueListByContact(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDuelistByContactId($this->contactId, $queryString);
        $pos_orders = [];
        $due_list = $result['list'];
        foreach ($due_list as $key => $item) {
            if ($item["attachments"]) {
                $item["attachments"] = is_array($item["attachments"]) ? $item["attachments"] : json_decode($item["attachments"]);
            }
            $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
            $item['entry_at'] = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
            if ($item['source_id'] && $item['source_type'] == EntryTypes::POS) {
                $pos_orders[] =  $item['source_id'];
            }
            $due_list[$key]['partner_wise_order_id']= null;
        }

        if (count($pos_orders) > 0) {
            $orders = $this->getPartnerWise($pos_orders)['orders'];
        }

        foreach ($due_list as $key => $val) {
            if ($val['source_id'] && $val['source_type'] == EntryTypes::POS && count($orders) > 0) {

                $due_list[$key]['partner_wise_order_id'] = $orders[$val['source_id']]['partner_wise_order_id'];
            }
        }
        return [
            'list' => $due_list
        ];
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
    public function downloadPDF($request)
    {
        $queryString = $this->generateQueryString();
        $data = [];
        $data['start_date'] = $this->start_date ?? null;
        $data['end_date']   = $this->end_date ?? null;
        if($this->contactId == null){
            $list = $this->dueTrackerRepo->setPartner($this->partner)->searchDueList($queryString);
            $data = array_merge($data, $list);
            $balanceData = $this->getDueListBalance();
            $data = array_merge($data, $balanceData);
            return (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list')->save(true);
        }

        $list = $this->dueTrackerRepo->setPartner($this->partner)->getDuelistByContactId($this->contactId, $queryString);
        $data = array_merge($data, $list);
        $balanceData = $this->setCustomerId($request->contact_id)->dueListBalanceByContact();
        $data = array_merge($data, $balanceData);
        return (new PdfHandler())->setName("due tracker by customer")->setData($data)->setViewFile('due_tracker_due_list_by_customer')->save(true);
    }

    /**
     * @return string
     */
    private function generateQueryString(): string
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

        if (isset($this->start_date) && isset($this->end_date)) {
            $query_strings [] = "start_date=$this->start_date";
            $query_strings [] = "end_date=$this->end_date";
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

    /**
     * @param $partner
     * @return array
     */
    private function getPartnerInfo($partner): array
    {
        return [
            'name' => $partner->name,
            'avatar' => $partner->logo,
            'mobile' => $partner->mobile,
        ];
    }

    /**
     * @param $pos_orders
     * @return mixed
     * @throws \App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError
     */
    private function getPartnerWise($pos_orders)
    {
        /** @var OrderServiceAlias $orderService */
        $orderService= app(OrderServiceAlias::class);
        return $orderService->getPartnerWiseOrderIds('[' . implode(",",$pos_orders) . ']' ,0,count($pos_orders));

    }
}