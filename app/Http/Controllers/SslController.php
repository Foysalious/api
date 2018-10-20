<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;

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
}