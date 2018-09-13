<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\PayCharge\PayCharge;

class SslController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            $pay_charge = new PayCharge('online');
            if ($response = $pay_charge->complete($request->tran_id)) {
                return redirect($response['redirect_url'] . '?invoice_id=' . $request->tran_id);
            } else {
                return api_response($request, null, 400, ['message' => $pay_charge->message]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


}