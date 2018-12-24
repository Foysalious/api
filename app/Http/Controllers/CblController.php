<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;


class CblController extends Controller
{
    public function validateCblPGR(Request $request)
    {
        try {
            $this->validate($request, [
                'xmlmsg' => 'required|string',
            ]);
            $xml = simplexml_load_string($request->xmlmsg);
            $invoice = "SHEBA_CBL_" . $xml->SessionId->__toString();
            $payment = Payment::where('transaction_id', $invoice)->valid()->first();
            if (!$payment) return redirect(config('sheba.front_url'));
            $sheba_payment = new ShebaPayment('cbl');
            $payment = $sheba_payment->complete($payment);
            $payable = $payment->payable;
            return redirect($payable->success_url . '?invoice_id=' . $invoice);
        } catch (\Throwable $e) {
            $xml = simplexml_load_string($request->xmlmsg);
            $invoice = "SHEBA_CBL_" . $xml->SessionID->__toString();
            $payment = Payment::where('transaction_id', $invoice)->valid()->first();
            app('sentry')->captureException($e);
            return redirect($payment->payable->success_url . '?invoice_id=' . $invoice);
        }
    }
}