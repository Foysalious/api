<?php namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;
use Throwable;

class UpayController extends Controller
{
    /**
     * @throws InvalidPaymentMethod
     * @throws Throwable
     * @throws AlreadyCompletingPayment
     */
    public function validatePayment(Request $request, PaymentManager $manager)
    {
        $this->validate($request, ['invoice_id'=>'required']);
        $payment = Payment::where('gateway_transaction_id', $request->invoice_id)->first();
        if (empty($payment)) return api_response($request, null, 404,['message'=>'payment not found']);
        if (!$payment->isValid() || $payment->isComplete()) {
            return api_response($request, null, 402, ['message' => "Invalid or completed payment"]);
        }
        $method=PaymentStrategy::UPAY;
        $manager->setMethodName($method)->setPayment($payment)->complete();
        $payment->reload();
        $redirect_url = $payment->status === Statuses::COMPLETED ?
            $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id :
            $payment->payable->fail_url . '?invoice_id=' . $payment->transaction_id;
        return redirect()->to($redirect_url);
    }
}