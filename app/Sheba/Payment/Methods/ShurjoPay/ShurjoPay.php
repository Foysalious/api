<?php namespace Sheba\Payment\Methods\ShurjoPay;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Ssl\Response\InitResponse;
use Sheba\Payment\Statuses;

class ShurjoPay extends PaymentMethod
{

    public function init(Payable $payable): Payment
    {
        $payment       = $this->createPayment($payable, $this->store->getName());
        $response      = $this->createSslSession($payment);
        $init_response = new InitResponse();
        $init_response->setResponse($response);

        if ($init_response->hasSuccess()) {
            $success                      = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->redirect_url        = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to'                  => Statuses::INITIATION_FAILED,
                'from'                => $payment->status,
                'transaction_details' => json_encode($error->details)
            ]);
            $payment->status              = Statuses::INITIATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    private function get

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }
}