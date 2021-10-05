<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;
use Sheba\Notification\SeenBy;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Throwable;

class NotificationController extends Controller
{
    /**
     * @param Request $request
     * @param MemberRepositoryInterface $member_repository
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function index(Request $request, MemberRepositoryInterface $member_repository, TimeFrame $time_frame)
    {
        $this->validate($request, ['limit' => 'numeric', 'offset' => 'numeric']);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        list($offset, $limit) = calculatePagination($request);
        /** @var Member $member */
        $member = $member_repository->find($business_member['member_id']);
        $time_frame = $time_frame->forTwoDates(Carbon::now()->subDays(30)->toDateString(), Carbon::now()->toDateString());

        $notifications = $member->notifications()
            ->sortLatest()
            ->dateBetween('created_at', $time_frame)
            ->skip($offset)
            ->limit($limit)
            ->get();

        $final = [];
        $notifications->each(function ($notification) use (&$final) {
            array_push($final, [
                'id' => $notification->id,
                'message' => $notification->title,
                'type' => $notification->getType(),
                'type_id' => $notification->event_id,
                'is_seen' => $notification->is_seen,
                'created_at' => $notification->created_at->toDateTimeString()
            ]);
        });

        if (count($final) == 0) return api_response($request, null, 404);
        return api_response($request, null, 200, ['notifications' => $final]);
    }

    public function lastNotificationCount(Request $request, MemberRepositoryInterface $member_repository)
    {
        $this->validate($request, [
            'time' => 'required',
        ]);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);
        $member = $member_repository->find($business_member['member_id']);

        $notifications_count = $member->notifications()->whereIn('event_type', [
            'Sheba\Dal\Announcement\Announcement',
            'Sheba\Dal\ApprovalRequest\Model',
            'Sheba\Dal\Leave\Model',
            'Sheba\Dal\Support\Model',
            'Sheba\Dal\Payslip\Payslip',
        ])->where('created_at', '>=', $request->time)->where('is_seen', 0)->count();

        return api_response($request, null, 200, ['notifications' => $notifications_count]);
    }

    public function seen(Request $request, SeenBy $seenBy, MemberRepositoryInterface $member_repository)
    {
        try {
            $this->validate($request, ['notifications' => 'required|string',]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->find($business_member['member_id']);
            $seenBy->setNotifications(json_decode($request->notifications))->setUser($member)->seen();
            return api_response($request, null, 200);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function test(Request $request, PushNotificationHandler $pushNotificationHandler)
    {
        $this->validate($request, [
            'support_id' => 'sometimes|required|numeric',
            'announcement_id' => 'sometimes|required|numeric',
            'attendance' => 'sometimes|required|numeric',
            'leave_request_id' => 'sometimes|required|numeric',
            'leave_id' => 'sometimes|required|numeric',
            'cancel_leave_id' => 'sometimes|required|numeric',
            'homepage' => 'sometimes|required',
            'payslip_id' => 'sometimes|required|numeric',
            'schedule_date' => 'sometimes|required',
        ]);

        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        $topic = config('sheba.push_notification_topic_name.employee') . (int)$business_member['member_id'];
        $channel = config('sheba.push_notification_channel_name.employee');
        $sound  = config('sheba.push_notification_sound.employee');

        if ($request->has('homepage')) {
            $pushNotificationHandler->send([
                "title" => 'Refer digiGO & Earn up to 10000 TK',
                "message" => "Refer digiGO & win attractive referral fee. Just provide lead information & we do the rest. Visit digiGO Now!",
                "event_type" => 'referral',
                "event_id" => '',
                "time" => Carbon::now(),
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('support_id')) {
            $pushNotificationHandler->send([
                "title" => 'New support created',
                "message" => "Test support",
                "event_type" => 'support',
                "event_id" => $request->support_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('announcement_id')) {
            $pushNotificationHandler->send([
                "title" => 'New announcement arrived',
                "message" => "Test announcement",
                "event_type" => 'announcement',
                "event_id" => $request->announcement_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('attendance')) {
            $pushNotificationHandler->send([
                "title" => 'Attendance Alert',
                "message" => "Have you reached office yet?  You are 5 minutes behind from being late! Hurry up!",
                "event_type" => 'attendance',
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('leave_request_id')) {
            $pushNotificationHandler->send([
                "title" => 'New Leave Request Arrived',
                "message" => "Leave Request Arrived Message",
                "event_type" => 'leave_request',
                "event_id" => $request->leave_request,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('leave_id')) {
            $pushNotificationHandler->send([
                "title" => 'Substitute Setup',
                "message" => "AI choose you a substitute",
                "event_type" => 'substitute',
                "event_id" => $request->leave_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('cancel_leave_id')) {
            $pushNotificationHandler->send([
                "title" => "leave cancel",
                "message" => "Test canceled his leave",
                "event_type" => 'leave',
                "event_id" => $request->leave_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('payslip')) {
            $pushNotificationHandler->send([
                "title" => "Payslip Disbursed",
                "message" => "Payslip Disbursed of month ".Carbon::parse($request->schedule_date)->format('M Y'),
                "event_type" => 'payslip',
                "event_id" => $request->payslip_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }
        if ($request->has('appreciation')) {
            $pushNotificationHandler->send([
                "title" => 'Appreciation',
                "message" => "Test appreciated you",
                "event_type" => 'appreciation',
                "event_id" => $request->appreciation_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
        }

        return api_response($request, null, 200);
    }
}
