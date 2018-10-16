<?php

namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class PartnerNotificationController extends Controller
{

    public function update($partner, Request $request, NotificationRepository $notificationRepository)
    {
        try {
            $partner = $request->partner;
            $notifications = collect($request->notification)->map(function ($notification) {
                return (int)$notification;
            })->toArray();
            $notifications = $partner->notifications()->whereIn('id', $notifications)->get();
            if (count($notifications) == 0) return api_response($request, null, 404);
            $notificationRepository->updateSeenBy($request->manager_resource, $notifications);
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}