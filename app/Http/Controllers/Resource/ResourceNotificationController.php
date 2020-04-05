<?php namespace App\Http\Controllers\Resource;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Notification\SeenBy;
use Sheba\PushNotificationHandler;

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

        if($request->has('job_id')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title" => 'কাজ আসাইন',
                "message" => 'আপনাকে একটি অর্ডার ' . $job->partnerOrder->order->code() . ' এ এসাইন করা হয়েছে',
                "event_type" => 'job_assign',
                "event_id" => $request->job_id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }

        if($request->has('payment')) {
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

        if($request->has('job_alert')) {
            $job = Job::find($request->job_id);
            $pushNotificationHandler->send([
                "title"     => "তৈরি হয়ে নিন",
                "message" => 'আপনার অর্ডার' . $job->partnerOrder->order->code() . 'টি আর ১৫ মিনিট এর মধ্যে শুরু হয়ে যাবে',
                "event_type"=> "job_alert",
                "event_id"  => $job->id,
                "sound"     => 'notification_sound',
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }
    }

    public function index(Request $request)
    {
        $this->validate($request, ['limit' => 'numeric', 'offset' => 'numeric']);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        list($offset, $limit) = calculatePagination($request);
        $notifications = $resource->notifications()->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
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
