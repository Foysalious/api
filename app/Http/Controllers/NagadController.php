<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Payment\Methods\Nagad\Nagad;
use Sheba\Payment\Methods\Nagad\Validator;
use Sheba\Payment\PaymentManager;

class NagadController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $paymentManager)
    {
        try {
            $this->validate($request, ['order_id' => 'required', 'payment_ref_id' => 'required']);
            $data      = $request->all();
            $validator = new Validator($data);
            $payment   = $validator->getPayment();
            $method    = $payment->paymentDetails->last()->method;
            dd($method);
            if ($method !== 'nagad') throw new \Exception('Invalid Method completion');
            /** @var Nagad $method */
            $method = $paymentManager->setPayment($payment)->setMethodName('nagad')->getMethod();
            $method->setRefId($validator->getPaymentRefId());
            $paymentManager->complete();
            $redirect_url = $payment->payable->success_url;
            return redirect()->to($redirect_url);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
