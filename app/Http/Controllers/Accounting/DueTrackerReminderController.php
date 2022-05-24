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

    public function __construct(DueTrackerReminderService $dueTrackerReminderService)
    {
        $this->dueTrackerReminderService = $dueTrackerReminderService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'contact_id' => 'required',
            'contact_type' => 'required|in:customer,supplier',
            'should_send_sms' => 'required',
            'reminder_date' => 'required|date_format:Y-m-d H:i:s',
        ]);

        try {
            $response = $this->dueTrackerReminderService
                ->setPartner($request->partner)
                ->setContactType($request->contact_type)
                ->setContactId($request->contact_id)
                ->setSms($request->should_send_sms)
                ->setReminderDate($request->reminder_date)
                ->createReminder();
            return http_response($request, null, 200, ['data' => $response]);
        }
        catch (AccountingEntryServerError $e){
            if($e->getCode() == 400){
                return http_response($request, null, 400, ['data' => $e->getMessage()]);
            }
            else{
                throw $e;
            }
        }

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
            'should_send_sms' => 'required|integer',
            'reminder_date' => 'required|date_format:Y-m-d H:i:s',
            'reminder_status' => 'required|in:success,pending,failed',
            'sms_status' => 'required|in:success,pending,failed'
        ]);
        $response = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setReminderId($request->reminder_id)
            ->setSms($request->should_send_sms)
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     */
    public function reminderNotificationWebhook(Request $request): JsonResponse
    {
        if ($request->api_key != 'sheba_xyz_acc_key') {
            return http_response($request, null, 400, ['message' => 'Invalid Request!']);
        }
        $reminder['id'] = $request->id;
        $reminder['partner_id'] = $request->partner_id;
        $reminder['contact_info'] = $request->contact_info;
        $reminder['contact_type'] = $request->contact_type;
        $reminder['reminder_at'] = $request->reminder_at;
        $reminder['should_send_sms'] = $request->should_send_sms;
        $reminder['reminder_status'] = $request->reminder_status;
        $reminder['sms_status'] = $request->sms_status;
        $reminder['balance'] = $request->balance;
        $reminder['balance_type'] = $request->balance_type;

        $response = $this->dueTrackerReminderService->sendReminderPush($reminder);
        return http_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function statusCount(Request $request){
        $this->validate($request, [
            'contact_type' => 'required|in:customer,supplier',
        ]);
        $data = $this->dueTrackerReminderService
            ->setPartner($request->partner)
            ->setReminderStatus($request->reminder_status)
            ->setContactType($request->contact_type)
            ->getStatusCount();

        return http_response($request, null, 200, ['data' => $data]);

    }
}