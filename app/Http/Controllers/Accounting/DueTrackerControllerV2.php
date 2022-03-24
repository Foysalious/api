<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mpdf\MpdfException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Usage\Usage;

class DueTrackerControllerV2 extends Controller
{
    /** @var DueTrackerService */
    protected $dueTrackerService;

    public function __construct(DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerService = $dueTrackerService;
    }

    /**
     * @throws AccountingEntryServerError
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
        $response = $this->dueTrackerService
            ->setPartner($request->partner)
            ->setAmount($request->amount)
            ->setEntryType($request->source_type)
            ->setAccountKey($request->account_key)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setDate($request->entry_at)
            ->setPartnerWiseOrderId($request->partner_wise_order_id)
            ->setAttachments($request->attachments)
            ->setNote($request->note)
            ->storeEntry();
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->auth_user);
        return api_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(Request $request): JsonResponse
    {
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
     * @throws PosOrderServiceServerError
     */
    public function dueListByContact(Request $request): JsonResponse
    {
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
     */
    public function getReport(Request $request): JsonResponse
    {
        $data = $this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->report();
        return http_response($request, null, 200, ['data' => $data]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     * @throws MpdfException
     * @throws InvalidPartnerPosCustomer
     * @throws NotAssociativeArray
     * @throws \Throwable
     */
    public function downloadPdf(Request $request): JsonResponse
    {
        $data=$this->dueTrackerService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->downloadPDF($request);
        return http_response($request, null, 200, ['message' => 'PDF download successful', 'pdf_link' => $data]);
    }

}