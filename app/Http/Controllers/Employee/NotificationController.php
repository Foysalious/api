<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Notification\SeenBy;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Throwable;

class NotificationController extends Controller
{
    /**
     * @param Request $request
     * @param MemberRepositoryInterface $member_repository
     * @return JsonResponse
     */
    public function index(Request $request, MemberRepositoryInterface $member_repository)
    {
        $this->validate($request, ['limit' => 'numeric', 'offset' => 'numeric']);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $member = $member_repository->find($business_member['member_id']);
        $notifications = $member->notifications()->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
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
            'leave_request_id' => 'sometimes|required|numeric'
        ]);

        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        $topic = config('sheba.push_notification_topic_name.employee') . (int)$business_member['member_id'];
        $channel = config('sheba.push_notification_channel_name.employee');

        if ($request->has('support_id')) {
            $pushNotificationHandler->send([
                "title" => 'New support created',
                "message" => "Test support",
                "event_type" => 'support',
                "event_id" => $request->support_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
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
            ], $topic, $channel);
        }

        if ($request->has('attendance')) {
            $pushNotificationHandler->send([
                "title" => 'Attendance Alert',
                "message" => "Have you reached office yet?  You are 5 minutes behind from being late! Hurry up!",
                "event_type" => 'attendance',
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
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
            ], $topic, $channel);
        }

        return api_response($request, null, 200);
    }
}
