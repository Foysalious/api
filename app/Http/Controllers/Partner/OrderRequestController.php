<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;

class OrderRequestController extends Controller
{
    public function lists($partner, Request $request)
    {
        try {
            $orders = [];
            return api_response($request, null, 200, ['orders' => $orders]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
