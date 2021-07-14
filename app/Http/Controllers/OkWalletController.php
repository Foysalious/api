<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;

class OkWalletController extends Controller
{
    /**
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Sheba\Payment\Exceptions\AlreadyCompletingPayment
     * @throws \Sheba\Payment\Exceptions\InvalidPaymentMethod
     * @throws \Throwable
     */
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $this->validate($request, ['order_id' => 'required']);

        /** @var Payment $payment */
        $payment = Payment::where('transaction_id', $request->order_id)->first();

        if (!$payment->isValid() || $payment->isComplete()) {
            return api_response($request, null, 402, ['message' => "Invalid or completed payment"]);
        }

        $payment_manager->setMethodName(PaymentStrategy::OK_WALLET)->setPayment($payment);
        $payment = $payment_manager->complete() ?: $payment;
        $redirect_url = $payment->status === Statuses::COMPLETED
            ? $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id
            : $payment->payable->fail_url . '?invoice_id=' . $payment->transaction_id;

        return redirect()->to($redirect_url);
    }
}
