<?php namespace App\Http\Controllers;

use App\Sheba\Notification\Customer\NotificationHandler;
use App\Models\Transport\TransportTicketOrder;
use App\Models\MovieTicketOrder;
use Illuminate\Http\Request;
use App\Models\TopUpOrder;

class CustomerNotificationController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $notifications = (new NotificationHandler)
                ->setCustomer($customer)
                ->notification('order', 'top_up', 'movie_ticket', 'transport_ticket');
            $notifications = collect($notifications)->sortByDesc('updated_at')->values()->take(30);
            return api_response($request, null, 200, ['notifications' => $notifications]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $notifications = collect($request->notification);
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
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}