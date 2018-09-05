<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Sheba\PayCharge\PayCharge;

class SslController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            $payment_info = $data = Cache::store('redis')->get("paycharge::$request->tran_id");
            $payment_info = json_decode($payment_info);
            $pay_chargable = unserialize($payment_info->pay_chargable);
            $pay_charge = new PayCharge('online');
            if ($pay_charge->complete($payment_info)) {
                Cache::store('redis')->forget("paycharge::$request->tran_id");
                return redirect($pay_chargable->redirectUrl);
            } else  return api_response($request, null, 500, ['message' => $pay_charge->message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}