<?php namespace App\Http\Controllers\Resource;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\PushNotificationHandler;

class ResourceNotificationController extends Controller
{
    public function test(Request $request, PushNotificationHandler $pushNotificationHandler)
    {
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
    }
}
