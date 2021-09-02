<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Payment\Methods\Nagad\Nagad;
use Sheba\Payment\Methods\Nagad\Validator;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;

class NagadController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $paymentManager)
    {
        try {
            $this->validate($request, ['order_id' => 'required', 'payment_ref_id' => 'required']);
            $data      = $request->all();
            $validator = new Validator($data);
            $payment   = $validator->getPayment();
            if (!$payment->isValid()||$payment->isComplete()){
                return api_response($request, null, 402,['message'=>"Invalid or completed payment"]);
            }
            $method    = $payment->paymentDetails->last()->method;
            if ($method !== 'nagad') throw new \Exception('Invalid Method completion');
            /** @var Nagad $method */
            $method = $paymentManager->setPayment($payment)->setMethodName($method)->getMethod();
            $method->setRefId($validator->getPaymentRefId());
            $payment      = $paymentManager->complete() ?: $payment;
            $redirect_url = $payment->status === Statuses::COMPLETED
                ? $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id
                : $payment->payable->fail_url . '?invoice_id=' . $payment->transaction_id;
            return redirect()->to($redirect_url);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
