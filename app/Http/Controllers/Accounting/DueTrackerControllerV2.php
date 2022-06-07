<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\AccountKeyTypes;
use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Service\DueTrackerReportService;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use App\Sheba\AccountingEntry\Service\DueTrackerSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mpdf\MpdfException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Usage\Usage;
use Throwable;

class DueTrackerControllerV2 extends Controller
{
    protected $dueTrackerService;
    protected $dueTrackerSmsService;
    protected $dueTrackerReportService;

    public function __construct(DueTrackerService $dueTrackerService, DueTrackerReportService $dueTrackerReportService)
    {
        $this->dueTrackerService = $dueTrackerService;
        $this->dueTrackerReportService = $dueTrackerReportService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required',
            'source_type' => 'required|in:due,deposit',
            'account_key' => 'sometimes|string',
            'contact_type' => 'required|string',
            'contact_id' => 'required',
            'note' => 'sometimes',
            'entry_at' => 'required|date_format:Y-m-d H:i:s',
            'partner_wise_order_id' => 'sometimes|numeric',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'sometimes|mimes:jpg,jpeg,png,bmp|max:2048'
        ]);

        $entry_dto = app()->make(EntryDTO::class);
        $entry_dto->setAmount($request->amount)
            ->setSourceType($request->source_type)
            ->setAccountKey($request->account_key)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setContactName($request->contact_name)
            ->setContactMobile($request->contact_mobile)
            ->setContactProPic($request->contact_pro_pic)
            ->setEntryAt($request->entry_at)
            ->setAttachments($request->attachments)
            ->setNote($request->note);
        $response = $this->dueTrackerService->setPartner($request->partner)->setEntryDto($entry_dto)->storeEntry();
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->auth_user);
        return http_response($request, null, 201, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @param int $entry_id
     * @return JsonResponse
     */
    public function update(Request $request, int $entry_id): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required',
            'source_type' => 'required|in:due,deposit',
            'account_key' => 'sometimes|string',
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get()),
            'contact_id' => 'required',
            'note' => 'sometimes',
            'entry_at' => 'sometimes|date_format:Y-m-d H:i:s',
            'attachment_should_remove' => 'sometimes|array',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'sometimes|mimes:jpg,jpeg,png,bmp|max:2048'

        ]);
        $entry_dto = app()->make(EntryDTO::class);
        $entry_dto->setAmount($request->amount)
            ->setSourceType($request->source_type)
            ->setAccountKey($request->account_key)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setEntryAt($request->entry_at)
            ->setAttachments($request->attachments)
            ->setNote($request->note)
            ->setEntryId($entry_id);
        $response = $this->dueTrackerService->setPartner($request->partner)->setEntryDto($entry_dto)->updateEntry();
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_ENTRY_UPDATE)->create($request->auth_user);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function badDebts(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string',
            'contact_id' => 'required',
        ]);
        $entry_dto = app()->make(EntryDTO::class);
        $entry_dto->setSourceType(EntryTypes::DEPOSIT)
            ->setAccountKey(AccountKeyTypes::CASH)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setNote('অনাদায়ী পাওনা');

        $response = $this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setEntryDTO($entry_dto)
            ->badDebts();

        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->auth_user);
        return http_response($request, null, 201, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string'
        ]);
        $response = $this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->getDueListBalance();
        return http_response($request, null, 200, ['data' => $response]);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function dueList(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string'
        ]);
        $data=$this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setOrder($request->order)
            ->setOrderBy($request->order_by)
            ->setBalanceType($request->balance_type)
            ->setQuery($request->q)
            ->setOffset($request->offset)
            ->setLimit($request->limit)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->getDueList();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function dueListByContact(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string',
        ]);
        $data=$this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contactId)
            ->setOrder($request->order)
            ->setOrderBy($request->order_by)
            ->setBalanceType($request->balance_type)
            ->setQuery($request->q)
            ->setOffset($request->offset)
            ->setLimit($request->limit)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->dueListByContact();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     * @throws InvalidPartnerPosCustomer
     */
    public function dueListBalanceByContact(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string',
        ]);
        $data = $this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contactId)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->dueListBalanceByContact();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     * @throws MpdfException
     * @throws InvalidPartnerPosCustomer
     * @throws NotAssociativeArray
     * @throws Throwable
     */
    public function downloadPdf(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string',
        ]);
        $data=$this->dueTrackerReportService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->downloadPDF();
        return http_response($request, null, 200, ['message' => 'PDF download successful', 'pdf_link' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function publicReport(Request $request): JsonResponse
    {
        $this->validate($request,[
            'contact_type' => 'required|string',
        ]);
        $data = $this->dueTrackerReportService
            ->setPartnerId($request->partner_id)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->generatePublicReport();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return void
     * @throws AccountingEntryServerError
     * @throws MpdfException
     * @throws NotAssociativeArray
     * @throws Throwable
     */
    public function publicReportDownload(Request $request){
        $data=$this->dueTrackerReportService
            ->setPartnerById($request->partner_id)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->downloadPDF();
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename='".$request->partner_id.$request->contact_type.$request->contact_id."_report.pdf'");
        readfile($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function getReport(Request $request): JsonResponse
    {
        $data = $this->dueTrackerReportService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contactId)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setLimit($request->limit)
            ->setOffset($request->offset)
            ->getReport();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDateRangeFilter(Request $request): JsonResponse
    {
        $filters = ['today', 'week', 'month', 'quarter', 'year', 'yesterday', 'last_week',
            'last_month', 'last_quarter', 'last_year', 'by_date'] ;
        $response = [];
        foreach ($filters as $filter) {
            $date_range = getRangeFormat(['range' => $filter]);
            $response[$filter] = [
                'key' => $filter,
                'title' => dateRangeTitleBn($filter, $date_range),
                'start_date' => $date_range[0]->format('Y-m-d'),
                'end_date' => $date_range[1]->format('Y-m-d'),
            ];
        }
        return http_response($request, null, 200,
            [ 'data' => ['filter' => array_values($response)]  ]);
    }

}