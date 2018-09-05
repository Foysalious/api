<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Sheba\PayCharge\PayCharge;

class SslController extends Controller
{
    public function validate(Request $request)
    {
        try {
            $payment_info = $data = Cache::store('redis')->get("paycharge::$request->tran_id");
            $payment_info = json_decode($payment_info);
            $pay_charge = new PayCharge('online');
            if ($pay_charge->complete($payment_info)) return api_response($request, 1, 200);
            else  return api_response($request, null, 500, ['message' => $pay_charge->message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}