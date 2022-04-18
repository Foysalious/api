<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\DueTrackerReminderRepository;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\Pos\Order\PosOrderObject;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use App\Sheba\PosOrderService\Services\OrderService as OrderServiceAlias;
use App\Sheba\Reports\DueTracker\AccountingPdfHandler;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Illuminate\Support\Collection;
use Mpdf\MpdfException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Order\PosOrderResolver;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;
use Exception;
use Throwable;
use App\Models\Partner;
use Sheba\Helpers\Converters\NumberLanguageConverter;
class DueTrackerService
{
    protected $partner;
    protected $reminderRepo;
    protected $dueTrackerRepo;
    protected $contact_type;
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
    protected $contact_id;
    protected $note;
    protected $partner_id;

    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo,DueTrackerReminderRepository $reminderRepo)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
        $this->reminderRepo = $reminderRepo;
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

    /**
     * @param $contact_id
     * @return $this
     */
    public function setContactId($contact_id): DueTrackerService
    {
        $this->contact_id = $contact_id;
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
     * @param $partner_id
     * @return $this
     */
    public function setPartnerId($partner_id): DueTrackerService
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $contact_type
     * @return DueTrackerService
     */
    public function setContactType($contact_type): DueTrackerService
    {
        $this->contact_type = $contact_type;
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
     * @param $note
     * @return $this
     */
    public function setNote($note): DueTrackerService
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function storeEntry()
    {
        $data = $this->makeDataForEntry();
        return $this->dueTrackerRepo->createEntry($data);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function badDebts(){
        $queryString = $this->generateQueryString();
        $balance = $this->dueTrackerRepo->setPartner($this->partner)->dueListBalanceByContact($this->contact_id, $queryString);

        if($balance['stats']['type'] == 'receivable') {
            $this->amount = $balance['stats']['balance'];
            $data = $this->makeDataForEntry();
            return $this->dueTrackerRepo->createEntry($data);
        }
        return "Balance is Already Positive.";
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDueListBalance($queryString);
        $return_data = $result;
        $return_data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');
        if ($this->contact_type == ContactType::SUPPLIER) {
            $supplier_due = $this->dueTrackerRepo->setPartner($this->partner)->getSupplierMonthlyDue();
            $return_data['supplier_due'] = $supplier_due['due'];
        }
        return $return_data;
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
     * @return array
     * @throws InvalidPartnerPosCustomer|AccountingEntryServerError
     */
    public function dueListBalanceByContact(): array
    {
        $contact_balance = $this->getBalanceByContact();
        try {
            $reminder = $this->reminderRepo->setPartner($this->partner)->reminderByContact($this->contact_id, $this->contact_type);
        }catch (Exception $e) {
            $reminder = [];
        }
        return [
            'contact_details' => $contact_balance['contact_details'],
            'stats' => $contact_balance['stats'],
            'other_info' => $contact_balance['other_info'],
            'reminder' => $reminder,
            'current_time' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @return array
     * @throws AccountingEntryServerError
     */
    public function dueListByContact(): array
    {
        $queryString = $this->generateQueryString();
        $result = $this->dueTrackerRepo->setPartner($this->partner)->getDuelistByContactId($this->contact_id, $queryString);
        $due_list = $result['list'];
        $pos_orders = [];
        /*
        collect($due_list)->each(function ($each) use (&$pos_orders) {
            if (!is_null($each['source_id']) && $each['source_type'] == EntryTypes::POS) {
                $pos_orders [] = $each['source_id'];
            }
        });
        if (count($pos_orders) > 0) {
            $orders = $this->getPartnerWisePosOrders($pos_orders)['orders'];
        }
        foreach ($due_list as $key => &$item) {
            if ($item['source_id'] && $item['source_type'] == EntryTypes::POS && isset($orders[$item['source_id']])) {
                $order = $orders[$item['source_id']];
                $item['partner_wise_order_id'] = $order['partner_wise_order_id'];
                $item['source_type'] = 'PosOrder';
                $item['head'] = 'POS sales';
                $item['head_bn'] = 'সেলস';
                if (isset($order['sales_channel']) == SalesChannels::WEBSTORE) {
                    $item['source_type'] = 'Webstore Order';
                    $item['head'] = 'Webstore sales';
                    $item['head_bn'] = 'ওয়েবস্টোর সেলস';
                }
            }
        }
        */
        return [
            'list' => $due_list
        ];
    }

    /**
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getReport(): array
    {
        $queryString = $this->generateQueryString();
        return $this->dueTrackerRepo->setPartner($this->partner)->getReportForMobile($queryString);
    }

    /**
     * @return string
     * @throws AccountingEntryServerError
     * @throws MpdfException
     * @throws NotAssociativeArray
     * @throws Throwable
     */
    public function downloadPDF(): string
    {
        $queryString = $this->generateQueryString();
        $data = [];

        $start_date = date_create($this->start_date);
        $end_date = date_create($this->end_date);

        $data['data']['start_date'] = ($this->start_date != null) ? NumberLanguageConverter::en2bn(date_format($start_date,"d")).' '.banglaMonth(date_format($start_date,"m")).' '.NumberLanguageConverter::en2bn(date_format($start_date,"Y")) : '';
        $data['data']['end_date'] = ($this->end_date != null ? NumberLanguageConverter::en2bn(date_format($end_date,"d")).' '.banglaMonth(date_format($end_date,"m")).' '.NumberLanguageConverter::en2bn(date_format($end_date,"Y")) : '');
        $data['data']['now'] = DayTimeConvertBn(date("Y-m-d H:i:s"));

        $data['data']['partner']['name'] = $this->partner->name;
        $data['data']['partner']['mobile'] = $this->partner->mobile;
        $data['data']['partner']['logo'] = $this->partner->logo;

        if ($this->contact_id == null) {
            $list = $this->dueTrackerRepo->setPartner($this->partner)->getDueListFromAcc($queryString);
            $data = array_merge($data, $list);
            $balanceData = $this->getDueListBalance();
            $data = array_merge($data, $balanceData);
            //TODO: Will Change the Pdf Generation
            return "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/invoices/pdf/20220310_due_tracker_report_1646895731.pdf";
            //return (new PdfHandler())->setName("due tracker")->setData($data)->setViewFile('due_tracker_due_list')->save(true);
        }
        $data['data'] += $this->dueTrackerRepo->setPartner($this->partner)->downloadPdfByContact($queryString);
        $data = $this->listBnForContactPdf($data);
        $header =  view('reports.pdfs.dueTrackerPartials._header_duelist_single_contact', compact('data'))->render();
        $footer = view('reports.pdfs.dueTrackerPartials._footer_duelist_single_contact')->render();

        return (new AccountingPdfHandler())->setHeader($header)
            ->setFooter($footer)
            ->setName("due tracker by contact")
            ->setData($data)
            ->setViewFile('due_tracker_due_list_by_contact')
            ->save(true,$header);
    }

    /**
     * @return array|mixed
     * @throws AccountingEntryServerError
     */
    public function generatePublicReport(){
        $queryString = $this->generateQueryString();
        $data = $this->dueTrackerRepo->reportForWeb($this->partner_id,$queryString);

        $data['stats']['receivable_bn'] = NumberLanguageConverter::en2bn($data['stats']['receivable']);
        $data['stats']['payable_bn'] = NumberLanguageConverter::en2bn($data['stats']['payable']);
        $data['stats']['balance_bn'] = NumberLanguageConverter::en2bn($data['stats']['balance']);

        foreach($data['list'] as $key => $value){
            $date = date_create($data['list'][$key]['entry_at']);
            $data['list'][$key]['amount_bn'] = NumberLanguageConverter::en2bn($data['list'][$key]['amount']);
            $data['list'][$key]['entry_at_bn'] = NumberLanguageConverter::en2bn(date_format($date,"d")).' '.banglaMonth(date_format($date,"m")).' '.NumberLanguageConverter::en2bn(date_format($date,"Y")) ;
            $data['list'][$key]['balance_bn'] = NumberLanguageConverter::en2bn($data['list'][$key]['balance']);
        }

        $this->getPartnerById();
        $partnerInfo = $this->getPartnerInfo($this->partner);

        $data['partner_info'] = $partnerInfo;
        $data['partner_info']['contact_type'] = $this->contact_type;
        return $data;
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

        if (isset($this->contact_type)) {
            $query_strings [] = "contact_type=" . strtolower($this->contact_type);
        }

        if (isset($this->contact_id)) {
            $query_strings [] = "contact_id=" . strtolower($this->contact_id);
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
    public function getPartnerInfo($partner): array
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
     * @throws PosOrderServiceServerError
     */
    private function getPartnerWisePosOrders($pos_orders)
    {
        /** @var OrderServiceAlias $orderService */
        $orderService = app(OrderServiceAlias::class);
        return $orderService->getPartnerWiseOrderIds('[' . implode(",", $pos_orders) . ']', 0, count($pos_orders));
    }

    /**
     * @param $partner
     * @param $partnerWiseOrderId
     * @return PosOrderObject
     */
    private function posOrderByPartnerWiseOrderId($partner, $partnerWiseOrderId)
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setPartnerWiseOrderId($partner->id, $partnerWiseOrderId)->get();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    private function makeDataForEntry(): array
    {
        $posOrder = ($this->entry_type == EntryTypes::POS) ? $this->posOrderByPartnerWiseOrderId($this->partner, $this->partner_wise_order_id) : null;

        $data['contact_id'] = $this->contact_id;
        $data['customer_id'] = $this->contact_id; //TODO: Should remove when customer resolver fix from POS SIDE
        $data['contact_type'] = $this->contact_type;
        $data['amount'] = $this->amount;
        $data['entry_at'] = $this->date;
        $data['source_type'] = $this->entry_type;
        $data['to_account_key'] = $this->entry_type === EntryTypes::DUE ? $this->contact_id : $this->account_key;
        $data['from_account_key'] = $this->entry_type === EntryTypes::DUE ? (new Accounts())->income->sales::DUE_SALES_FROM_DT : $this->contact_id;
        $data['note'] = $this->note;
        $data['partner'] = $this->partner;
        $data['attachments'] = $this->attachments;
        $data['source_id'] = $posOrder ? $posOrder->id : null;

        return $data;
    }

    /**
     * @return void
     */
    private function getPartnerById(){
        $partner = Partner::where('id', $this->partner_id)->first();
        $this->setPartner($partner);
    }

    /**
     * @param $data
     * @return array
     */
    private function listBnForContactPdf($data): array
    {
        $list = array();
        foreach($data['data']['due_list'] as $key => $value){
            $split = explode("-",$key);
            $keybn = banglaMonth($split[1]).' '.NumberLanguageConverter::en2bn($split[0]);
            foreach($value['list'] as $key1 => $v){
                $entry_at = date_create($data['data']['due_list'][$key]['list'][$key1]['entry_at']);
                $created_at = date_create($data['data']['due_list'][$key]['list'][$key1]['created_at']);
                $list[$keybn]['list'][$key1]['amount_bn'] = NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['list'][$key1]['amount']);
                $list[$keybn]['list'][$key1]['balance_bn'] = NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['list'][$key1]['balance']);
                $list[$keybn]['list'][$key1]['entry_at_bn'] = NumberLanguageConverter::en2bn(date_format($entry_at,"d")).'/'.NumberLanguageConverter::en2bn(date_format($entry_at,"m"));
                $list[$keybn]['list'][$key1]['created_at_bn'] = NumberLanguageConverter::en2bn(date_format($created_at,"d")).' '.banglaMonth(date_format($created_at,"m")).' '.NumberLanguageConverter::en2bn(date_format($created_at,"Y")) ;
                $list[$keybn]['list'][$key1]['note'] = $data['data']['due_list'][$key]['list'][$key1]['note'];
                $list[$keybn]['list'][$key1]['account_type'] = $data['data']['due_list'][$key]['list'][$key1]['account_type'];
            }
            $list[$keybn]['stats']['receivable_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['receivable']);
            $list[$keybn]['stats']['payable_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['payable']);
            $list[$keybn]['stats']['total_transactions_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['total_transactions']);
        }
        $data['data']['due_list_bn']=$list;
        return $data;
    }


    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function getBalanceByContact()
    {
        $queryString = $this->generateQueryString();
        $contact_balance =  $this->dueTrackerRepo
            ->setPartner($this->partner)
            ->dueListBalanceByContact($this->contact_id, $queryString);
        $customer = $contact_balance['contact_details'];
        if (is_null($customer)) {
            /** @var PosCustomerResolver $posCustomerResolver */
            $posCustomerResolver = app(PosCustomerResolver::class);
            $posCustomer = $posCustomerResolver->setCustomerId($this->contact_id)->setPartner($this->partner)->get();
            if (empty($posCustomer)) {
                throw new InvalidPartnerPosCustomer();
            }
            $customer['id'] = $posCustomer->id;
            $customer['name'] = $posCustomer->name;
            $customer['mobile'] = $posCustomer->mobile;
            $customer['pro_pic'] = $posCustomer->pro_pic;
        }
        $contact_balance['contact_details'] = $customer;
        return $contact_balance;
    }


}