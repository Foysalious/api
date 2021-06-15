<?php


namespace App\Http\Controllers;


use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;
use Throwable;

class EblController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $paymentManager)
    {
        try {
            $this->validate($request, [
                'signature'            => 'required',
                'signed_field_names'   => 'required',
                'req_transaction_uuid' => 'required',
                'req_reference_number' => 'required',
            ]);
            $payment = Payment::where('gateway_transaction_id', $request->req_reference_number)->first();
            if (!empty($payment)) {
                $payment      = $paymentManager->setMethodName(PaymentStrategy::EBL)->setPayment($payment)->complete();
                $redirect_url = $payment->status === Statuses::COMPLETED
                    ? $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id
                    : $payment->payable->fail_url . '?invoice_id=' . $payment->transaction_id;
                return redirect()->to($redirect_url);
            }

            return api_response($request, null, 404, ['message' => 'Payment not found to validate']);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 404, ['message' => $e->getMessage()]);
        }
    }
}