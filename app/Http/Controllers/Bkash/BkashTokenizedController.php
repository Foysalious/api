<?php


namespace App\Http\Controllers\Bkash;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Bkash\Modules\Tokenized\TokenizedPayment;
use Sheba\Bkash\ShebaBkash;
use Sheba\Settings\Payment\PaymentSetting;

class BkashTokenizedController extends Controller
{

    public function tokenizePayment(Request $request)
    {
        try {
            $this->validate($request, ['paymentID' => 'required']);
            /** @var Payment $payment */
            $payment = Payment::where('transaction_id', $request->paymentID)->first();
            if (!$payment) return api_response($request, null, 404, ['message' => 'Valid Payment not found.']);
            /** @var TokenizedPayment $tokenized_payment */
            $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
            $data = $tokenized_payment->create($payment);
            $payment->transaction_id = $data->paymentID;
            $payment->redirect_url = $data->bkashURL;
            $payment->transaction_details = json_encode($data);
            $payment->update();
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function validateAgreement(Request $request, PaymentSetting $paymentSetting)
    {
        try {
            $paymentSetting->setMethod('bkash')->save($request->paymentID);
            $key = 'order_' . $request->paymentID;
            $order = Redis::get($key);
            return $order ? redirect(config('sheba.front_url') . '/bkash?paymentID=' . (json_decode($order))->payment_id) : redirect(config('sheba.front_url') . '/profile/me');
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}