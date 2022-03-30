<?php namespace App\Http\Controllers\Payment\Bkash;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\Bkash\Modules\Tokenized\TokenizedPayment;
use Sheba\Bkash\ShebaBkash;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Settings\Payment\PaymentSetting;
use Sheba\Transactions\InvalidTransaction;
use Throwable;
use function api_response;
use function getValidationErrorMessage;
use function logError;
use function redirect;

class BkashTokenizedController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $this->validate($request, ['paymentID' => 'required']);
        /** @var Payment $payment */
        $payment = Payment::where('gateway_transaction_id', $request->paymentID)->valid()->first();

        if (!$payment) return api_response($request, null, 404, ['message' => 'Valid Payment not found.']);
        $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;

        try {
            $payment_manager->setMethodName(PaymentStrategy::BKASH)->setPayment($payment)->complete();
            return redirect($redirect_url);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return redirect($redirect_url);
        } catch (InvalidTransaction $e) {
            logError($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            logError($e);
            return redirect($redirect_url);
        }
    }

    public function tokenizePayment(Request $request)
    {
        try {
            $this->validate($request, ['paymentID' => 'required']);
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->paymentID)->first();
            if (!$payment) return api_response($request, null, 404, ['message' => 'Valid Payment not found.']);
            /** @var TokenizedPayment $tokenized_payment */
            $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
            $data = $tokenized_payment->create($payment);
            $payment->gateway_transaction_id = $data->paymentID;
            $payment->redirect_url = $data->bkashURL;
            $payment->transaction_details = json_encode($data);
            $payment->update();
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function validateAgreement(Request $request, PaymentSetting $paymentSetting)
    {
        try {
            $paymentSetting->setMethod('bkash')->save($request->paymentID);
            $key = 'order_' . $request->paymentID;
            $order = Redis::get($key);
            return $order ? redirect(config('sheba.front_url') . '/bkash?paymentID=' . (json_decode($order))->gateway_transaction_id) : redirect(config('sheba.front_url') . '/profile/me');
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
