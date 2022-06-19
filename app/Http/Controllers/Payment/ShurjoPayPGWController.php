<?php namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;

class ShurjoPayPGWController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $this->validate($request, ['order_id' => 'required']);
        $redirect_url = config('sheba.front_url');
        try {
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->order_id)->first();
            if (!$payment) throw new \Exception('Payment not found to validate.');
            $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;
            if ($payment->isValid() && !$payment->isComplete()) {
                $payment_manager->setMethodName(PaymentStrategy::SHURJOPAY)->setPayment($payment)->complete();
            }
        } catch (\Throwable $e) {
            logError($e);
        }
        return redirect($redirect_url);
    }
}