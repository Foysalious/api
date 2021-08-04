<?php namespace App\Http\Controllers\Employee;

use Sheba\Business\NotificationHistory\Updater as NotificationHistoryUpdater;
use App\Sheba\Business\BusinessBasicInformation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class NotificationHistoryController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /**  @var NotificationHistoryUpdater $notificationHistoryUpdater */
    private $notificationHistoryUpdater;

    /**
     * NotificationHistoryController constructor.
     * @param NotificationHistoryUpdater $notification_history_updater
     */
    public function __construct(NotificationHistoryUpdater $notification_history_updater)
    {
        $this->notificationHistoryUpdater = $notification_history_updater;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|in:success'
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $business_member->member;
        $this->setModifier($member);
        $this->notificationHistoryUpdater->setStatus($request->status)->updateStatus();

        return api_response($request, null, 200);
    }
}