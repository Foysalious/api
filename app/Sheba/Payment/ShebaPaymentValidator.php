<?php namespace Sheba\Payment;


use App\Models\Payment;
use Carbon\Carbon;

class ShebaPaymentValidator
{
    private $payableType;
    private $payableTypeId;
    private $paymentMethod;

    public function setPayableType($type)
    {
        $this->payableType = $type;
        return $this;
    }

    public function setPayableTypeId($type_id)
    {
        $this->payableTypeId = $type_id;
        return $this;
    }

    public function setPaymentMethod($payment_method)
    {
        $this->paymentMethod = $payment_method;
        return $this;
    }

    public function canInitiatePayment()
    {
        $time = Carbon::now()->subMinutes(1);
        $payment = Payment::whereHas('payable', function ($q) {
            $q->where([['type', $this->payableType], ['type_id', $this->payableTypeId]]);
        })->where([['transaction_id', 'LIKE', '%' . $this->paymentMethod . '%'], ['created_at', '>=', $time]])->first();
        return $payment ? 0 : 1;
    }
}