<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Service\DueTrackerReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class DueTrackerReminderController extends Controller
{
    protected $dueTrackerReminderService;

    public function __construct(DueTrackerReminderService $dueTrackerReminderService){
        $this->dueTrackerReminderService = $dueTrackerReminderService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function store(Request $request): JsonResponse
    {
        //TODO: sms key should change
        $this->validate($request, [
            'contactId' => 'required',
            'contact_type' => 'required|in:customer,supplier',
            'sms' => 'required',
            'reminder_date' => 'required|date_format:Y-m-d H:i:s',
        ]);
        $response = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contactId)
            ->setSms($request->sms)
            ->setReminderDate($request->reminder_date)
            ->createReminder();
        return http_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function reminders(Request $request): JsonResponse
    {
        $data = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setOffset($request->offset)
            ->setLimit($request->limit)
            ->setOrderBy($request->order_by)
            ->setReminderStatus($request->reminder_status)
            ->setContactType($request->contact_type)
            ->getReminders();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function update(Request $request): JsonResponse
    {
        $this->validate($request, [
            'sms' => 'required|integer',
            'reminder_date' => 'required|date_format:Y-m-d H:i:s',
            'reminder_status' => 'required|integer',
            'sms_status' => 'required|integer'
        ]);
        $response = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setReminderId($request->reminder_id)
            ->setSms($request->sms)
            ->setReminderDate($request->reminder_date)
            ->setReminderStatus($request->reminder_status)
            ->setSmsStatus($request->sms_status)
            ->update();
        return http_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function delete(Request $request): JsonResponse
    {
        $response = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setReminderId($request->reminder_id)
            ->delete();
        return http_response($request, null, 200, ['data' => $response]);
    }
}