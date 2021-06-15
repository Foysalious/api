<?php namespace App\Http\Controllers\PaymentLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentLinkBillRequest;
use App\Sheba\Payment\Adapters\Payable\PaymentLinkOrderAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Sheba\Customer\Creator;
use Illuminate\Http\Request;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Dal\ExternalPayment\Model as ExternalPayment;

class PaymentLinkBillController extends Controller
{
    /**
     * @param PaymentLinkBillRequest                        $request
     * @param PaymentManager                 $payment_manager
     * @param PaymentLinkOrderAdapter        $payment_adapter
     * @param Creator                        $customer_creator
     * @param PaymentLinkRepositoryInterface $repo
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearBill(PaymentLinkBillRequest $request, PaymentManager $payment_manager, PaymentLinkOrderAdapter $payment_adapter,
                              Creator $customer_creator, PaymentLinkRepositoryInterface $repo)
    {
        $payment_method = $request->payment_method;
        $user           = $customer_creator->setMobile($request->mobile)->setName($request->name)->create();
        $payment_link   = $repo->findByIdentifier($request->identifier);

        if (!empty($payment_link->getEmiMonth()) && (double)$payment_link->getAmount() < config('emi.manager.minimum_emi_amount'))
            return api_response($request, null, 400, ['message' => 'Amount must be greater then or equal BDT ' . config('emi.manager.minimum_emi_amount')]);

        $payable = $payment_adapter->setPayableUser($user)->setPaymentLink($payment_link)
                                   ->setAmount($request->amount)->setDescription($request->purpose)
                                   ->getPayable();
        if ($payment_method == 'wallet' && $user->shebaCredit() < $payable->amount)
            return api_response($request, null, 403, ['message' => "You don't have sufficient balance"]);

        if ($payment_link->getEmiMonth()){
            $bank = $payment_manager->getEmibank($request->bank_id);
            $payment_method = $bank->paymentGateway->method_name ?? PaymentStrategy::SSL;
        } elseif($request->payment_method == 'online') {
            $cardType = $payment_manager->getCardType($request->card_number);
            $payment_method = $cardType->paymentGateway->method_name ?? PaymentStrategy::SSL;
        }

        $payment = $payment_manager->setMethodName($payment_method)->setPayable($payable)->init();
        $target  = $payment_link->getTarget();
        if ($target instanceof ExternalPayment) {
            $target->payment_id = $payment->id;
            $target->update();
        }
        return response()->json([
            'code' => 200,
            'message' => 'Successful',
            'payment' => $payment->getFormattedPayment()
        ]);
//        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }
}
