<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use App\Sheba\AccountingEntry\Service\DueTrackerSmsService;
use Illuminate\Http\Request;

class DueTrackerSmsController extends Controller
{
    protected $dueTrackerSmsService;
    protected $dueTrackerService;

    public function __construct(DueTrackerSmsService $dueTrackerSmsService, DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerSmsService = $dueTrackerSmsService;
        $this->dueTrackerService = $dueTrackerService;
    }

    public function reminderSmsWebhook(Request $request)
    {
        if ($request->api_key != config('accounting_entry.api_key')) {
            return http_response($request, null, 400, ['message' => 'Invalid Request!']);
        }
        $sms_content["balance"] = $request->balance;
        $sms_content["balance_type"] = $request->balance_type;
        $sms_content["contact_name"] = $request->contact_name;
        $sms_content["contact_mobile"] = $request->contact_mobile;
        $response = $this->dueTrackerSmsService->setPartnerId($request->partner_id)
            ->setContactType($request->contact_type)
            ->sendSmsForReminder($sms_content);
        return http_response($request, null, 200, ['data' => $response]);

    }
}