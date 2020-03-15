<?php namespace App\Http\Controllers;

use App\Models\Payment;
use App\Sheba\Payment\Methods\OkWallet\Request\InitRequest;
use Illuminate\Http\Request;
use Sheba\Payment\ShebaPayment;

class OkWalletController extends Controller
{
    const NAME = 'ok_wallet';

    /**
     * @param Request $request
     * @param ShebaPayment $sheba_payment
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function validatePayment(Request $request, ShebaPayment $sheba_payment)
    {

        $redirect_url = config('sheba.front_url');
        try {
            $request = (new InitRequest(json_decode($request->data,true)));
            $payment = Payment::where('gateway_transaction_id', $request->getSessionKey())->first();
            if ($payment) {
                $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;

                if ($payment->isValid() && !$payment->isComplete())
                    $sheba_payment->setMethod(self::NAME)->complete($payment);
            } else {
                throw new \Exception('Payment not found to validate.');
            }

        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
        }
        return redirect($redirect_url);
    }
}