<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;

class CblController extends Controller
{
    public function validateCblPGR(Request $request, PaymentManager $payment_manager)
    {
        $xml = simplexml_load_string($request->xmlmsg);
        $invoice = "SHEBA_CBL_" . $xml->OrderID->__toString();
        $payment = Payment::where('gateway_transaction_id', $invoice)->valid()->first();
        if (!$payment) return redirect(config('sheba.front_url'));

        if (!$payment->isValid()||$payment->isComplete()){
            return api_response($request, null, 402,['message'=>"Invalid or completed payment"]);
        }
        try {
            $this->validate($request, [
                'xmlmsg' => 'required|string',
            ]);
            $payable = $payment->payable;
            $payment = $payment_manager->setMethodName(PaymentStrategy::CBL)->setPayment($payment)->complete();
            return redirect($payable->success_url . '?invoice_id=' . $payment->transaction_id);
        } catch (\Throwable $e) {
            logError($e);
            if (!$payment) return redirect(config('sheba.front_url'));
            return redirect($payment->payable->success_url . '?invoice_id=' . $payment->transaction_id);
        }
    }
}
