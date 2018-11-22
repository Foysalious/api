<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;
use Sheba\TopUp\Vendor\Internal\SslClient;

class SslController extends Controller
{
    public function validatePayment(Request $request)
    {
        try {
            if (empty($request->headers->get('referer'))) {
                return api_response($request, null, 400);
            };
            $payment = Payment::where('transaction_id', $request->tran_id)->valid()->first();
            if (!$payment) return redirect(config('sheba.front_url'));
            $sheba_payment = new ShebaPayment('online');
            $payment = $sheba_payment->complete($payment);
            $payable = $payment->payable;
            return redirect($payable->success_url . '?invoice_id=' . $request->tran_id);
        } catch (\Throwable $e) {
            $payment = Payment::where('transaction_id', $request->tran_id)->valid()->first();
            app('sentry')->captureException($e);
            return redirect($payment->payable->success_url . '?invoice_id=' . $request->tran_id);
        }
    }

    public function validateTopUp(Request $request)
    {
        try {
            $ssl = new SslClient();
            $response = $ssl->getRecharge($request->vr_guid);
            return $response;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}