<?php namespace App\Sheba\Payment\Complete;


use Sheba\ModificationFields;
use Sheba\Payment\Complete\PaymentComplete;

class PaymentLinkOrderComplete extends PaymentComplete
{

    use ModificationFields;

    public function complete()
    {
        $has_error = false;
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($customer = $payable->user);
            $this->payment->transaction_details = null;
            $this->completePayment();
        } catch (RequestException $e) {
            $this->failPayment();
            throw $e;
        }
        if ($has_error) {
            $this->completePayment();
        }
        return $this->payment;
    }
}
