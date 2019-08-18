<?php namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use App\Sheba\Payment\Adapters\Payable\PaymentLinkOrderAdapter;
use Sheba\Customer\Creator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Payment\ShebaPayment;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class PaymentLinkBillController extends Controller
{
    public function clearBill(Request $request, PaymentLinkOrderAdapter $paymentLinkOrderAdapter, Creator $customerCreator, PaymentLinkRepositoryInterface $paymentLinkRepository)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|in:online,bkash,cbl',
                'amount' => 'numeric',
                'purpose' => 'string',
                'identifier' => 'required',
                'name' => 'required',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $payment_method = $request->payment_method;
            $user = $customerCreator->setMobile($request->mobile)->setName($request->name)->create();
            $payment_link = $paymentLinkRepository->findByIdentifier($request->identifier);
            $payable = $paymentLinkOrderAdapter->setPayableUser($user)
                ->setPaymentLink($payment_link)->setAmount($request->amount)->setDescription($request->purpose)->getPayable();
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
