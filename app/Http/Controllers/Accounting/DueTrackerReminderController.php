<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Service\DueTrackerReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DueTrackerReminderController extends Controller
{
    protected $dueTrackerReminderService;

    public function __construct(DueTrackerReminderService $dueTrackerReminderService){
        $this->dueTrackerReminderService = $dueTrackerReminderService;
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'partner' => 'required',
            'contact_type' => 'required|in:customer,supplier',
            'sms' => 'required',
            'reminder_date' => 'required|date_format:Y-m-d',
            'reminder_status' => 'required',
            'sms_status' => 'required'
        ]);
        $response = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contactId)
            ->setSms($request->sms)
            ->setReminderDate($request->reminder_date)
            ->setReminderStatus($request->reminder_status)
            ->setSmsStatus($request->sms_status)
            ->createReminder();
        return http_response($request, null, 200, ['data' => $response]);

    }
}