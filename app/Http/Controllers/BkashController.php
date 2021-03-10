<?php namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Transactions\InvalidTransaction;
use Throwable;

class BkashController extends Controller
{
    public function validatePayment(Request $request, PaymentManager $payment_manager)
    {
        try {
            $this->validate($request, ['paymentID' => 'required']);
            /** @var Payment $payment */
            $payment = Payment::where('gateway_transaction_id', $request->paymentID)->valid()->first();
            if (!$payment) return api_response($request, null, 404, ['message' => 'Valid Payment not found.']);
            if (!$payment->isValid()||$payment->isComplete()){
                return api_response($request, null, 402,['message'=>"Invalid or completed payment"]);
            }
            $payment = $payment_manager->setPayment($payment)->setMethodName(PaymentStrategy::BKASH)->complete();

            $redirect_url = $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id;
            if ($payment->isComplete()) {
                return api_response($request, 1, 200, ['payment' => ['redirect_url' => $redirect_url]]);
            } elseif ($payment->isFailed()) {
                return api_response($request, null, 400, ['message' => 'Your payment has been failed due to ' . json_decode($payment->transaction_details)->errorMessage, 'payment' => array('redirect_url' => $redirect_url)]);
            } elseif ($payment->isPassed()) {
                return api_response($request, 1, 400, ['message' => 'Your payment has been received but there was a system error. It will take some time to update your transaction. Call 16516 for support.', 'payment' => array('redirect_url' => $redirect_url)]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400);
        } catch (InvalidTransaction $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentInfo($paymentID, Request $request)
    {
        $payment = Payment::where('gateway_transaction_id', $paymentID)->valid()->first();
        if (!$payment) return api_response($request, null, 404, ['message' => 'Payment not found.']);
        $data = array_merge(collect(json_decode($payment->transaction_details))->toArray(), [
            'order_id' => $payment->payable->type_id,
            'order_type' => $payment->payable->type,
            'token' => $payment->payable->user->remember_token,
            'id' => $payment->payable->user->id,
            'redirect_url' => $payment->payable->success_url . '?invoice_id=' . $payment->transaction_id
        ]);
        return $payment ? api_response($request, $payment, 200, ['data' => $data]) : api_response($request, null, 404);
    }
}
