<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\Adapters\Payable\UtilityOrderAdapter;
use Sheba\Payment\ShebaPayment;

class UtilityController extends Controller
{

    public function clearBills($utility_order, Request $request, UtilityOrderAdapter $utility_order_adapter, ShebaPayment $sheba_payment)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,wallet,bkash,cbl',
                'user_id' => 'required|numeric',
                'user_type' => 'required|string|in:customer,partner,affiliate',
                'token' => 'required|string',
            ]);
            $payment_method = $request->payment_method;
            $user = "App\\Models\\" . ucfirst($request->user_type);
            $user = $user::where([['remember_token', $request->token], ['id', $request->user_id]])->first();
            $payable = $utility_order_adapter->setUtilityOrder($utility_order)->getPayable();
            if ($payment_method == 'wallet' && $user->shebaCredit() < $payable->amount) return api_response($request, null, 403, ['message' => "You don't have sufficient balance"]);
            $payment = $sheba_payment->setMethod($payment_method)->init($payable);
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
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