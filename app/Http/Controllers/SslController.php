<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Logs\ErrorLog;
use Sheba\Payment\PaymentManager;
use Sheba\TopUp\Vendor\Internal\SslClient;

class SslController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        $redirect_url = config('sheba.front_url');
        try {
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->tran_id)->first();
            if ($payment) {
                $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;
                $method       = $payment->paymentDetails->last()->method;
                if ($payment->isValid() && !$payment->isComplete()) {
                    $payment_manager->setMethodName($method)->setPayment($payment)->complete();
                }
            } else {
                throw new \Exception('Payment not found to validate.');
            }
        } catch (\Throwable $e) {
            logError($e);
        }
        return redirect($redirect_url);
    }

    public function validateTopUp(Request $request)
    {
        try {
            $this->validate($request, [
                'vr_guid' => 'required',
                'guid'    => 'required',
            ]);
            $ssl      = new SslClient();
            $response = $ssl->getRecharge($request->guid, $request->vr_guid);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function checkBalance(Request $request)
    {
        try {
            $ssl      = new SslClient();
            $response = $ssl->getBalance();
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
