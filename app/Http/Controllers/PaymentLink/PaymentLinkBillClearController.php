<?php namespace App\Http\Controllers\PaymentLink;


use App\Http\Controllers\Controller;
use App\Sheba\Payment\Adapters\Payable\PaymentLinkOrderAdapter;
use App\Sheba\Payment\Exceptions\PayableNotFound;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\ShebaPayment;

class PaymentLinkBillClearController extends Controller
{
    public function clearBill(Request $request, PaymentLinkOrderAdapter $paymentLinkOrderAdapter)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,wallet,bkash,cbl',
                'amount' => 'sometimes|required|numeric',
                'identifier' => 'required'
            ]);
            $payment_method = $request->payment_method;
            $user_type = "App\\Models\\" . ucfirst($request->type);
            $user = $request->user;
            try {
                $payable = $paymentLinkOrderAdapter->setUserType($user_type)->setUser($user)
                    ->setPaymentLink($request->identifier, $request->get('amount'))->getPayable();
            } catch (PayableNotFound $e) {
                return api_response($request, null, 404, ['message' => $e->getMessage()]);
            }
            if ($payment_method == 'wallet' && $user->shebaCredit() < $payable->amount) return api_response($request, null, 403, ['message' => "You don't have sufficient balance"]);
            $payment = (new ShebaPayment($payment_method))->init($payable);
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
