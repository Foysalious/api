<?php namespace App\Http\Controllers\Resource;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Notification\SeenBy;
use Sheba\PushNotificationHandler;
use Sheba\Resource\Notification\NotificationList;

class ResourceNotificationController extends Controller
{
    public function test(Request $request, PushNotificationHandler $pushNotificationHandler)
    {
        $this->validate($request, ['job_id' => 'required']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $topic = config('sheba.push_notification_topic_name.resource') . $resource->id;
        $channel = config('sheba.push_notification_channel_name.resource');

        if ($request->filled('job_id')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title" => 'কাজ এসাইন',
                "message" => 'আপনাকে একটি অর্ডার ' . $job->partnerOrder->order->code() . ' এ এসাইন করা হয়েছে',
                "event_type" => 'job_assign',
                "event_id" => $request->job_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }

        if ($request->filled('payment')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title" => 'Online Payment',
                "message" => 'আপনার অর্ডার ' . $job->partnerOrder->order->code() . ' টি Online Payment এর মাধ্যমে পরিশোধ করা হয়েছে ',
                "event_type" => 'online_payment',
                "event_id" => $job->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }

        if ($request->filled('job_alert')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title" => "তৈরি হয়ে নিন",
                "message" => 'আপনার অর্ডার ' . $job->partnerOrder->order->code() . ' টি আর ১৫ মিনিট এর মধ্যে শুরু হয়ে যাবে',
                "event_type" => "job_alert",
                "event_id" => $job->id,
                "sound" => 'notification_sound',
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }

        if ($request->filled('job_extend')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title" => 'Need Extra Time?',
                "message" => 'Do you need extra time for Job ID: ' . $job->fullCode(),
                "event_type" => 'job_extend',
                "event_id" => $job->id,
                "action" => 'time-extend',
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }
    }

    public function index(Request $request, NotificationList $notificationList)
    {
        $this->validate($request, ['limit' => 'numeric', 'offset' => 'numeric']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $today_final = $notificationList->setResource($resource)->getTodaysNotifications();
        $this_week_final = $notificationList->setResource($resource)->getThisWeeksNotifications();
        $this_month_final = $notificationList->setResource($resource)->getThisMonthsNotifications();
        $earlier_final = $notificationList->setResource($resource)->getEarlierNotifications();

        $data = [['title' => 'Today', 'notification_data' => $today_final], ['title' => 'This Week', 'notification_data' => $this_week_final], ['title' => 'This Month', 'notification_data' => $this_month_final], ['title' => 'Earlier', 'notification_data' => $earlier_final]];
        return api_response($request, $data, 200, ['notifications' => $data]);
    }

    public function seen(Request $request, SeenBy $seenBy)
    {
        $this->validate($request, ['notifications' => 'required|string',]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $seenBy->setNotifications(json_decode($request->notifications))->setUser($resource)->seen();
        return api_response($request, null, 200);
    }
}
