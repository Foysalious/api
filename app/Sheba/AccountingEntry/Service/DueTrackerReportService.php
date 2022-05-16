<?php

namespace App\Sheba\AccountingEntry\Service;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\Reports\DueTracker\AccountingPdfHandler;
use App\Sheba\UrlShortener\Sheba\UrlShortenerService;
use Mpdf\MpdfException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Helpers\Converters\NumberLanguageConverter;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Throwable;

class DueTrackerReportService
{


    /**
     * @var DueTrackerRepositoryV2
     */
    protected $dueTrackerRepo;
    protected $end_date;
    protected $start_date;
    protected $contact_id;
    protected $contact_type;
    protected $partner_id;
    protected $partner;
    protected $limit;
    protected $offset;

    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo){
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    /**
     * @param $start_date
     * @return DueTrackerReportService
     */
    public function setStartDate($start_date): DueTrackerReportService
    {
        $this->start_date = $start_date;
        return $this;
    }

    /**
     * @param $end_date
     * @return DueTrackerReportService
     */
    public function setEndDate($end_date): DueTrackerReportService
    {
        $this->end_date = $end_date;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit): DueTrackerReportService
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset): DueTrackerReportService
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $contact_id
     * @return DueTrackerReportService
     */
    public function setContactId($contact_id): DueTrackerReportService
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    /**
     * @param mixed $contact_type
     * @return DueTrackerReportService
     */
    public function setContactType($contact_type): DueTrackerReportService
    {
        $this->contact_type = $contact_type;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return DueTrackerReportService
     */
    public function setPartner($partner): DueTrackerReportService
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $partner_id
     * @return DueTrackerReportService
     */
    public function setPartnerId($partner_id): DueTrackerReportService
    {
        $this->partner_id = $partner_id;
        return $this;
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
        $data['data']['now'] = DayTimeConvertBn(date("Y-m-d h:i:s")).' | '.NumberLanguageConverter::en2bn(date("d")).' '.banglaMonth(date("m")).' '.NumberLanguageConverter::en2bn(date("Y")) ;
        $data['data']['contact_type'] = $this->contact_type;

        $data['data']['partner']['name'] = $this->partner->name;
        $data['data']['partner']['mobile'] = $this->partner->mobile;
        $data['data']['partner']['logo'] = $this->partner->logo;

        if ($this->contact_id == null) {
            $data['data'] += $this->dueTrackerRepo->setPartner($this->partner)->downloadPdfByContact($queryString);
            $data = $this->listBnForPdf($data);
            $header =  view('reports.pdfs.dueTrackerPartials._header_duelist_single_contact', compact('data'))->render();
            $footer = view('reports.pdfs.dueTrackerPartials._footer_duelist_single_contact')->render();
            return (new AccountingPdfHandler())->setHeader($header)
                ->setFooter($footer)
                ->setName("due tracker by contact")
                ->setData($data)
                ->setViewFile('due_tracker_due_list_v2')
                ->save(true,$header);
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
     * @return void
     */
    private function getPartnerById(){
        $partner = Partner::where('id', $this->partner_id)->first();
        $this->setPartner($partner);
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
     * @param $data
     * @return array
     */
    private function listBnForPdf($data): array
    {
        $list = array();
        foreach($data['data']['due_list'] as $key => $value){
            $split = explode("-",$key);
            $keybn = banglaMonth($split[1]).' '.NumberLanguageConverter::en2bn($split[0]);
            foreach($value['list'] as $key1 => $v){
                $entry_at = date_create($data['data']['due_list'][$key]['list'][$key1]['entry_at']);
                $list[$keybn]['list'][$key1]['contact_name'] = $data['data']['due_list'][$key]['list'][$key1]['contact_name'];
                $list[$keybn]['list'][$key1]['entry_at_bn'] = NumberLanguageConverter::en2bn(date_format($entry_at,"d")).'/'.NumberLanguageConverter::en2bn(date_format($entry_at,"m"));
                $list[$keybn]['list'][$key1]['balance_bn'] = NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['list'][$key1]['balance']);
                $list[$keybn]['list'][$key1]['balance_type'] = NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['list'][$key1]['balance_type']);
            }
            $list[$keybn]['stats']['total_transactions_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['total_transactions']);
            $list[$keybn]['stats']['balance'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['balance']);
            $list[$keybn]['stats']['receivable_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['receivable']);
            $list[$keybn]['stats']['payable_bn'] =  NumberLanguageConverter::en2bn($data['data']['due_list'][$key]['stats']['payable']);
        }
        $data['data']['due_list_bn']=$list;
        return $data;
    }

    public function getWebReportLink()
    {
        $partner_url = env('SHEBA_PARTNER_URL');
        $report_link = $partner_url  . "/due-tracker/{$this->partner->id}/report?contact_id=$this->contact_id" .
            "&contact_type=$this->contact_type";
        return app()->make(UrlShortenerService::class)->shortUrl($report_link);
    }

}