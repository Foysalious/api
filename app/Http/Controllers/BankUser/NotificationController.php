<?php

namespace App\Http\Controllers\BankUser;

use App\Repositories\BankUserNotificationRepository;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Throwable;

class NotificationController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationSeen(Request $request, $id)
    {
        try {
            $message = (new BankUserNotificationRepository())->setNotificationSeen($id);
            return api_response($request, $message, 200, ['data' => $message]);
        }
        catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
