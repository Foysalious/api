<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;


class CblController extends Controller
{
    public function validateCblPGR(Request $request)
    {
        $xml = simplexml_load_string($request->xmlmsg);
        $invoice = "SHEBA_CBL_" . $xml->OrderID->__toString();
        $payment = Payment::where('gateway_transaction_id', $invoice)->valid()->first();
        if (!$payment) return redirect(config('sheba.front_url'));
        try {
            $this->validate($request, [
                'xmlmsg' => 'required|string',
            ]);
            $sheba_payment = new ShebaPayment('cbl');
            $payment = $sheba_payment->complete($payment);
            $payable = $payment->payable;
            return redirect($payable->success_url . '?invoice_id=' . $invoice);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            if (!$payment) return redirect(config('sheba.front_url'));
            return redirect($payment->payable->success_url . '?invoice_id=' . $payment->transaction_id);
        }
    }
}