<?php namespace Sheba\Payment\Methods\ShurjoPay;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\DynamicStore;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Ssl\Response\InitResponse;
use Sheba\Payment\Statuses;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Transactions\Wallet\HasWalletTransaction;

class ShurjoPay extends PaymentMethod
{
    public function init(Payable $payable): Payment
    {
        $shurjo_pay = new ShurjoPayStore();
        $shurjo_pay->setPartner($this->getReceiver($payable));
        $shurjo_pay->getStoreAccount(PaymentStrategy::SHURJOPAY);
        $payment = $this->createPayment($payable, PaymentStrategy::SHURJOPAY);
        $response = $this->createSslSession($payment);
        $init_response = new InitResponse();
        $init_response->setResponse($response);

        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->redirect_url = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to' => Statuses::INITIATION_FAILED,
                'from' => $payment->status,
                'transaction_details' => json_encode($error->details)
            ]);
            $payment->status = Statuses::INITIATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    private function getReceiver(Payable $payable): HasWalletTransaction
    {
        $payment_link = $payable->getPaymentLink();
        return $payment_link->getPaymentReceiver();
    }

//    private function get

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }
}