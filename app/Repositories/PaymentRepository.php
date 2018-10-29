<?php

namespace App\Repositories;


use App\Models\Payment;
use App\Models\PaymentStatusChangeLog;
use Carbon\Carbon;
use Sheba\ModificationFields;

class PaymentRepository
{
    use ModificationFields;
    private $payment;

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function changeStatus(array $data)
    {
        $new_payment = new PaymentStatusChangeLog();
        $new_payment->from = isset($data['from']) ? $data['from'] : null;
        $new_payment->to = isset($data['to']) ? $data['to'] : null;
        $new_payment->log = isset($data['log']) ? $data['log'] : null;
        $new_payment->transaction_details = isset($data['transaction_details']) ? $data['transaction_details'] : null;
        $this->setModifier($this->payment->payable->user);
        $this->withCreateModificationField($new_payment);
        $new_payment->created_at = Carbon::now();
        $new_payment->payment_id = $this->payment->id;
        $new_payment->save();
        return $new_payment;
    }
}