<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\Adapters\Payable\UtilityOrderAdapter;
use Sheba\Payment\ShebaPayment;

class UtilityController extends Controller
{

    public function clearBills($customer, $utility_order, Request $request, UtilityOrderAdapter $utility_order_adapter)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,wallet,bkash,cbl',
            ]);
            $payable = $utility_order_adapter->setUtilityOrder($utility_order)->getPayable();
            $payment = (new ShebaPayment($request->payment_method))->init($payable);
            return api_response($request, $payment, 200, ['link' => $payment->redirect_url, 'payment' => $payment->getFormattedPayment()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}