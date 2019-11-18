<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Notification\SeenBy;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class NotificationController extends Controller
{

    public function index(Request $request, MemberRepositoryInterface $member_repository)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->find($business_member['member_id']);
            $notifications = $member->notifications;
            $final = [];
            $notifications->each(function ($notification) use (&$final) {
                array_push($final, [
                    'id' => $notification->id,
                    'message' => $notification->title,
                    'type' => strtolower(str_replace('App\Models\\', '', $notification->event_type)),
                    'type_id' => $notification->event_id,
                    'is_seen' => $notification->is_seen,
                    'created_at' => $notification->created_at->toDateTimeString()
                ]);
            });
            if (count($final) == 0) return api_response($request, null, 404);
            return api_response($request, null, 200, ['notifications' => $final]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}