<?php


namespace Sheba\Payment\Complete;


use App\Sheba\Repositories\UtilityOrderRepository;

class UtilityOrderComplete extends PaymentComplete
{

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
            (new UtilityOrderRepository())->CompletePayment($payable->type_id);
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

