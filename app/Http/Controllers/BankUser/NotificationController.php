<?php

namespace App\Http\Controllers\BankUser;

use App\Repositories\BankUserNotificationRepository;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Throwable;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $notifications = (new BankUserNotificationRepository())->getBankUserNotifications($request->user, $offset, $limit);
            return api_response($request, $notifications, 200, ['data' => $notifications]);
        }
        catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
