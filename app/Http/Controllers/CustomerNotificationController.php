<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{

    public function update($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $notifications = collect($request->notifications);
            $notifications = $notifications->map(function ($notification) {
                return (int)$notification;
            })->toArray();
            $notifications = $customer->notifications->whereIn('id', $notifications);
            if (count($notifications) == 0) {
                return api_response($request, null, 404);
            }
            foreach ($notifications as $notification) {
                $notification->timestamps = false;
                $notification->is_seen = 1;
                $notification->update();
            }
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}