<?php namespace App\Http\Controllers;

use App\Models\Payment;
use App\Sheba\Payment\Methods\OkWallet\Request\InitRequest;
use Illuminate\Http\Request;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;

class OkWalletController extends Controller
{
    /**
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $redirect_url = config('sheba.front_url');
        try {
            $request = (new InitRequest(json_decode($request->data,true)));
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->getSessionKey())->first();
            if ($payment) {
                $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;

                if ($payment->isValid() && !$payment->isComplete())
                    $payment_manager->setMethodName(PaymentStrategy::OK_WALLET)->setPayment($payment)->complete();
            } else {
                throw new \Exception('Payment not found to validate.');
            }

        } catch (\Throwable $e) {
            logError($e);
        }
        return redirect($redirect_url);
    }
}
