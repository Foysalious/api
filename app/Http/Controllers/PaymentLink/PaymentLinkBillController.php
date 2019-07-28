<?php namespace App\Http\Controllers\PaymentLink;


use App\Http\Controllers\Controller;
use App\Sheba\Payment\Adapters\Payable\PaymentLinkOrderAdapter;
use Sheba\Customer\Creator;
use Sheba\Payment\Exceptions\PayableNotFound;
use Sheba\Payment\Exceptions\PaymentAmountNotSet;
use Sheba\Payment\Exceptions\PaymentLinkInactive;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\ShebaPayment;

class PaymentLinkBillController extends Controller
{
    public function clearBill(Request $request, PaymentLinkOrderAdapter $paymentLinkOrderAdapter, Creator $customerCreator)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,bkash,cbl',
                'amount' => 'sometimes|required|numeric',
                'identifier' => 'required',
                'name' => 'required',
                'mobile' => 'required|string|mobile:bd',
                'purpose' => 'string'
            ]);
            $payment_method = $request->payment_method;
            $user = $customerCreator->setMobile($request->mobile)->setName($request->name)->create();
            try {
                $payable = $paymentLinkOrderAdapter->setUser($user)
                    ->setPaymentLink($request->identifier, $request->get('amount'))->setPurpose($request->purpose)->getPayable();
            } catch (PayableNotFound $e) {
                return api_response($request, null, 404, ['message' => $e->getMessage()]);
            } catch (PaymentAmountNotSet $e) {
                return api_response($request, null, 404, ['message' => $e->getMessage()]);
            } catch (PaymentLinkInactive $e) {
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
